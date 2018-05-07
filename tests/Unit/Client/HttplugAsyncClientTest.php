<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Client;

use Exception;
use Gamez\Psr\Log\TestLogger;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Http\Client\HttpAsyncClient;
use Http\Message\MessageFactory;
use Http\Promise\Promise;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use TechDeCo\ElasticApmAgent\Client\HttplugAsyncClient;
use TechDeCo\ElasticApmAgent\ClientConfiguration;
use TechDeCo\ElasticApmAgent\Exception\ClientException;
use TechDeCo\ElasticApmAgent\Message\Error as ErrorMessage;
use TechDeCo\ElasticApmAgent\Message\Log;
use TechDeCo\ElasticApmAgent\Message\Service;
use TechDeCo\ElasticApmAgent\Message\Timestamp;
use TechDeCo\ElasticApmAgent\Message\Transaction as TransactionMessage;
use TechDeCo\ElasticApmAgent\Message\VersionedName;
use TechDeCo\ElasticApmAgent\Request\Error;
use TechDeCo\ElasticApmAgent\Request\Transaction;
use function json_encode;

final class HttplugAsyncClientTest extends TestCase
{
    /**
     * @var TestLogger
     */
    private $logger;

    /**
     * @var ClientConfiguration
     */
    private $config;

    /**
     * @var HttpAsyncClient|ObjectProphecy
     */
    private $httpClient;

    /**
     * @var MessageFactory|ObjectProphecy
     */
    private $messageFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Promise|ObjectProphecy
     */
    private $promise;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @var Error
     */
    private $error;

    /**
     * @var HttplugAsyncClient
     */
    private $client;

    /**
     * @before
     */
    public function setUpDependencies(): void
    {
        $this->logger         = new TestLogger();
        $this->config         = (new ClientConfiguration('http://apm'))->authenticatedByToken('spear');
        $this->httpClient     = $this->prophesize(HttpAsyncClient::class);
        $this->messageFactory = $this->prophesize(MessageFactory::class);
        $this->request        = new Request('POST', 'http://apm');
        $this->promise        = $this->prophesize(Promise::class);
        $this->client         = new HttplugAsyncClient(
            $this->logger,
            $this->config,
            $this->httpClient->reveal(),
            $this->messageFactory->reveal()
        );

        $agent             = new VersionedName('alloy', '1');
        $service           = new Service($agent, 'focus');
        $message           = new TransactionMessage(12.5, Uuid::uuid4(), 'thunderjaw', new Timestamp(), 'beast');
        $this->transaction = new Transaction($service, $message);
        $log               = new Log('roar');
        $message           = ErrorMessage::fromLog($log, new Timestamp());
        $this->error       = new Error($service, $message);

        $this->messageFactory->createRequest(Argument::any(), Argument::any(), Argument::any(), Argument::any())
                             ->willReturn($this->request);
        $this->httpClient->sendAsyncRequest(Argument::any())
                         ->willReturn($this->promise->reveal());
    }

    /**
     * @return ObjectProphecy|Promise
     */
    private function setUpPromiseResponse(ResponseInterface $response)
    {
        return $this->promise->wait()->willReturn($response);
    }

    /**
     * @return ObjectProphecy|Promise
     */
    private function setUpPromisResponseOk()
    {
        return $this->setUpPromiseResponse(new Response(200, [], 'good'));
    }

    public function testSendTransactionSendsRightRequest(): void
    {
        $this->setUpPromisResponseOk();
        $encoded = json_encode($this->transaction);
        $this->messageFactory
            ->createRequest(
                'POST',
                'http://apm/v1/transactions',
                [],
                $encoded
            )
            ->shouldBeCalled()
            ->willReturn($this->request);
        $this->httpClient->sendAsyncRequest(Argument::type(RequestInterface::class))
                         ->shouldBecalled()
                         ->willReturn($this->promise->reveal());

        $this->client->sendTransaction($this->transaction);
    }

    public function testSendTransactionLogsAuthentication(): void
    {
        $this->setUpPromisResponseOk();

        $this->client->sendTransaction($this->transaction);

        self::assertTrue($this->logger->log->hasRecordsWithPartialMessage('authentication token'));
    }

    public function testSendTransactionLogsSendingAsyncRequest(): void
    {
        $this->setUpPromisResponseOk();

        $this->client->sendTransaction($this->transaction);

        self::assertTrue($this->logger->log->hasRecordsWithPartialMessage('Sending asynchronous request'));
    }

    public function testSendErrorSendsRightRequest(): void
    {
        $this->setUpPromisResponseOk();
        $encoded = json_encode($this->error);
        $this->messageFactory
            ->createRequest(
                'POST',
                'http://apm/v1/errors',
                [],
                $encoded
            )
            ->shouldBeCalled()
            ->willReturn($this->request);
        $this->httpClient->sendAsyncRequest(Argument::type(RequestInterface::class))
                         ->shouldBecalled()
                         ->willReturn($this->promise->reveal());

        $this->client->sendError($this->error);
    }

    public function testSendsAppropriateHeaders(): void
    {
        $this->setUpPromisResponseOk();
        $hasRightHeaders = function (RequestInterface $request): bool {
            return $request->getHeaderLine('Content-Type') === 'application/json' &&
                $request->getHeaderLine('Authorization') === 'Bearer spear';
        };
        $this->httpClient->sendAsyncRequest(Argument::that($hasRightHeaders))
                         ->shouldBeCalled()
                         ->willReturn($this->promise->reveal());

        $this->client->sendTransaction($this->transaction);
    }

    public function testHttpClientExceptionsGetTransformedAndLogs(): void
    {
        $this->expectException(ClientException::class);
        $this->setUpPromisResponseOk();

        $this->httpClient->sendAsyncRequest(Argument::any())
                         ->willThrow(new Exception('transform this'));

        $this->client->sendTransaction($this->transaction);

        self::assertTrue($this->logger->log->hasRecordsWithPartialMessage('error'));
    }

    public function testHttp200Response(): void
    {
        $this->setUpPromiseResponse(new Response(200, [], 'good'));
        $this->httpClient->sendAsyncRequest(Argument::any())
                         ->shouldBeCalled()
                         ->willReturn($this->promise->reveal());

        $this->client->sendTransaction($this->transaction);
        $this->client->waitForResponses();

        self::assertTrue($this->logger->log->hasRecordsWithPartialMessage('Waiting for response'));
        self::assertTrue($this->logger->log->hasRecordsWithPartialMessage('Successful response '));
        self::assertFalse($this->logger->log->hasRecordsWithPartialMessage('error'));
    }

    public function testHttp400ResponseThrowsException(): void
    {
        $this->expectException(ClientException::class);

        $this->setUpPromiseResponse(new Response(400, [], 'your bad'));
        $this->httpClient->sendAsyncRequest(Argument::any())
                         ->shouldBeCalled()
                         ->willReturn($this->promise->reveal());

        $this->client->sendTransaction($this->transaction);
        $this->client->waitForResponses();

        self::assertTrue($this->logger->log->hasRecordsWithPartialMessage('Waiting for response'));
        self::assertFalse($this->logger->log->hasRecordsWithPartialMessage('Successful response '));
        self::assertTrue($this->logger->log->hasRecordsWithPartialMessage('error'));
    }

    public function testHttp500ResponseThrowsException(): void
    {
        $this->expectException(ClientException::class);

        $this->setUpPromiseResponse(new Response(500, [], 'our bad'));
        $this->httpClient->sendAsyncRequest(Argument::any())
                         ->shouldBeCalled()
                         ->willReturn($this->promise->reveal());

        $this->client->sendTransaction($this->transaction);
        $this->client->waitForResponses();

        self::assertTrue($this->logger->log->hasRecordsWithPartialMessage('Waiting for response'));
        self::assertFalse($this->logger->log->hasRecordsWithPartialMessage('Successful response '));
        self::assertTrue($this->logger->log->hasRecordsWithPartialMessage('error'));
    }

    public function testAllPromisesAreResolved(): void
    {
        $responseA = new Response(500, [], 'your bad');
        $responseB = new Response(500, [], 'our bad');
        $responseC = new Response(200, [], 'good');

        $promiseA = $this->prophesize(Promise::class);
        $promiseA->wait()->willReturn($responseA);
        $promiseB = $this->prophesize(Promise::class);
        $promiseB->wait()->willReturn($responseB);
        $promiseC = $this->prophesize(Promise::class);
        $promiseC->wait()->willReturn($responseC);

        $this->httpClient->sendAsyncRequest(Argument::any())
                         ->shouldBeCalled()
                         ->willReturn(
                             $promiseA->reveal(),
                             $promiseB->reveal(),
                             $promiseC->reveal()
                         );

        $this->client->sendTransaction($this->transaction);
        $this->client->sendTransaction($this->transaction);
        $this->client->sendTransaction($this->transaction);

        try {
            $this->client->waitForResponses();
        } catch (ClientException $e) {
            self::assertCount(2, $e->getExceptionList());
        }
    }
}
