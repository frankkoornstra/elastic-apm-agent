<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Client;

use DateTimeImmutable;
use Exception;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Http\Client\HttpAsyncClient;
use Http\Message\MessageFactory;
use Http\Promise\Promise;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\RequestInterface;
use Ramsey\Uuid\Uuid;
use TechDeCo\ElasticApmAgent\AsyncClient;
use TechDeCo\ElasticApmAgent\Client\HttplugAsyncClient;
use TechDeCo\ElasticApmAgent\ClientConfiguration;
use TechDeCo\ElasticApmAgent\Exception\ClientException;
use TechDeCo\ElasticApmAgent\Message\Error as ErrorMessage;
use TechDeCo\ElasticApmAgent\Message\Log;
use TechDeCo\ElasticApmAgent\Message\Service;
use TechDeCo\ElasticApmAgent\Message\Transaction as TransactionMessage;
use TechDeCo\ElasticApmAgent\Message\VersionedName;
use TechDeCo\ElasticApmAgent\Request\Error;
use TechDeCo\ElasticApmAgent\Request\Transaction;
use function json_encode;

final class HttplugAsyncClientTest extends TestCase
{
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
     * @var AsyncClient
     */
    private $client;

    /**
     * @before
     */
    public function setUpDependencies(): void
    {
        $this->config         = (new ClientConfiguration('http://apm'))->authenticatedByToken('spear');
        $this->httpClient     = $this->prophesize(HttpAsyncClient::class);
        $this->messageFactory = $this->prophesize(MessageFactory::class);
        $this->request        = new Request('POST', 'http://apm');
        $this->promise        = $this->prophesize(Promise::class);
        $this->client         = new HttplugAsyncClient(
            $this->config,
            $this->httpClient->reveal(),
            $this->messageFactory->reveal()
        );

        $agent             = new VersionedName('alloy', '1');
        $service           = new Service($agent, 'focus');
        $message           = new TransactionMessage(12.5, Uuid::uuid4(), 'thunderjaw', new DateTimeImmutable(), 'beast');
        $this->transaction = new Transaction($service, $message);
        $log               = new Log('roar');
        $message           = ErrorMessage::fromLog($log, new DateTimeImmutable());
        $this->error       = new Error($service, $message);

        $this->messageFactory->createRequest(Argument::any(), Argument::any(), Argument::any(), Argument::any())
                             ->willReturn($this->request);
        $this->httpClient->sendAsyncRequest(Argument::any())
                         ->willReturn($this->promise->reveal());
    }

    public function testSendTransactionSendsRightRequest(): void
    {
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

        $this->client->sendTransactionAsync($this->transaction);
    }

    public function testSendErrorSendsRightRequest(): void
    {
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

        $this->client->sendErrorAsync($this->error);
    }

    public function testSendsAppropriateHeaders(): void
    {
        $hasRightHeaders = function (RequestInterface $request): bool {
            return $request->getHeaderLine('Content-Type') === 'application/json' &&
                $request->getHeaderLine('Authorization') === 'Bearer spear';
        };
        $this->httpClient->sendAsyncRequest(Argument::that($hasRightHeaders))
                         ->shouldBeCalled()
                         ->willReturn($this->promise->reveal());

        $this->client->sendTransactionAsync($this->transaction);
    }

    public function testHttpClientExceptionsGetTransformed(): void
    {
        $this->expectException(ClientException::class);

        $this->httpClient->sendAsyncRequest(Argument::any())
                         ->willThrow(new Exception('transform this'));

        $this->client->sendTransactionAsync($this->transaction);
    }

    public function testHttp200Response(): void
    {
        $response = new Response(200, [], 'good');
        $this->promise->wait()
                      ->willReturn($response);
        $this->httpClient->sendAsyncRequest(Argument::any())
                         ->shouldBeCalled()
                         ->willReturn($this->promise->reveal());

        $this->client->sendTransactionAsync($this->transaction);
        $this->client->waitForResponses();
    }

    public function testHttp400ResponseThrowsException(): void
    {
        $this->expectException(ClientException::class);

        $response = new Response(400, [], 'your bad');
        $this->promise->wait()
                      ->willReturn($response);
        $this->httpClient->sendAsyncRequest(Argument::any())
                         ->shouldBeCalled()
                         ->willReturn($this->promise->reveal());

        $this->client->sendTransactionAsync($this->transaction);
        $this->client->waitForResponses();
    }

    public function testHttp500ResponseThrowsException(): void
    {
        $this->expectException(ClientException::class);

        $response = new Response(500, [], 'our bad');
        $this->promise->wait()
                      ->willReturn($response);
        $this->httpClient->sendAsyncRequest(Argument::any())
                         ->shouldBeCalled()
                         ->willReturn($this->promise->reveal());

        $this->client->sendTransactionAsync($this->transaction);
        $this->client->waitForResponses();
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

        $this->client->sendTransactionAsync($this->transaction);
        $this->client->sendTransactionAsync($this->transaction);
        $this->client->sendTransactionAsync($this->transaction);

        try {
            $this->client->waitForResponses();
        } catch (ClientException $e) {
            self::assertCount(2, $e->getExceptionList());
        }
    }
}
