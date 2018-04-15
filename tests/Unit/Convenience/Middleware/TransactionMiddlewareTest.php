<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Convenience\Middleware;

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Ramsey\Uuid\Uuid;
use TechDeCo\ElasticApmAgent\AsyncClient;
use TechDeCo\ElasticApmAgent\Convenience\HttplugHttpClient\HttpClientWrapper;
use TechDeCo\ElasticApmAgent\Convenience\Middleware\TransactionMiddleware;
use TechDeCo\ElasticApmAgent\Convenience\OpenTransaction;
use TechDeCo\ElasticApmAgent\Message\Process;
use TechDeCo\ElasticApmAgent\Message\Service;
use TechDeCo\ElasticApmAgent\Message\System;
use TechDeCo\ElasticApmAgent\Message\VersionedName;
use TechDeCo\ElasticApmAgent\Request\Transaction;
use TechDeCo\ElasticApmAgent\Tests\Dummy\DummyHandler;

final class TransactionMiddlewareTest extends TestCase
{
    /**
     * @var DummyHandler
     */
    private $dummy;

    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * @var AsyncClient|ObjectProphecy
     */
    private $client;

    /**
     * @var Service
     */
    private $service;

    /**
     * @var Process
     */
    private $process;

    /**
     * @var System
     */
    private $system;

    /**
     * @var MiddlewareInterface
     */
    private $middleware;

    /**
     * @before
     */
    public function setUpDependencies(): void
    {
        $this->dummy      = new DummyHandler(1);
        $this->request    = new ServerRequest('GET', 'http://foo.bar');
        $this->client     = $this->prophesize(AsyncClient::class);
        $this->service    = new Service(new VersionedName('alloy', '1'), 'focus');
        $this->process    = new Process(3);
        $this->system     = (new System())->atHost('foo.bar');
        $this->middleware = new TransactionMiddleware(
            $this->client->reveal(),
            $this->service,
            $this->process,
            $this->system
        );
    }

    public function testForwardRequestWithTransactionAttribute(): void
    {
        $response    = $this->middleware->process($this->request, $this->dummy);
        $transaction = $this->dummy->request->getAttribute(TransactionMiddleware::TRANSACTION_ATTRIBUTE);

        self::assertInstanceOf(ResponseInterface::class, $response);
        self::assertInstanceOf(OpenTransaction::class, $transaction);
    }

    public function testSendsTransactionAndWaitsForResponsesWithNormalResponse(): void
    {
        $this->client->sendTransactionAsync(Argument::type(Transaction::class))
                     ->shouldBeCalled();
        $this->client->waitForResponses()
                     ->shouldBeCalled();

        $this->middleware->process($this->request, $this->dummy);
    }

    public function testSendsTransactionAndWaitsForResponsesWithException(): void
    {
        $this->expectException(\Throwable::class);
        $dummy = new DummyHandler(1, true);

        $this->client->sendTransactionAsync(Argument::type(Transaction::class))
                     ->shouldBeCalled();
        $this->client->waitForResponses()
                     ->shouldBeCalled();

        $this->middleware->process($this->request, $dummy);
    }

    public function testSentTransactionCapturesDuration(): void
    {
        $dummy             = new DummyHandler(15);
        $comesCloseToSleep = function (Transaction $transaction): bool {
            $data    = $transaction->jsonSerialize();
            $message = $data['transactions'][0];

            return $message['duration'] > 15 && $message['duration'] < 20;
        };

        $this->client->sendTransactionAsync(Argument::that($comesCloseToSleep))
                     ->shouldBeCalled();
        $this->client->waitForResponses()
                     ->shouldBeCalled();

        $this->middleware->process($this->request, $dummy);
    }

    /**
     * @dataProvider provideTransactionAssertions
     */
    public function testSentTransactionCaptures(callable $ensuresData): void
    {
        $packedEnsuresData = function (Transaction $transaction) use ($ensuresData): bool {
            $data = $transaction->jsonSerialize();

            return $ensuresData($data);
        };
        $this->client->sendTransactionAsync(Argument::that($packedEnsuresData))
                     ->shouldBeCalled();
        $this->client->waitForResponses()
                     ->shouldBeCalled();

        $this->middleware->process($this->request, $this->dummy);
    }

    /**
     * @return callable[][]
     */
    public function provideTransactionAssertions(): array
    {
        return [
            'service' => [
                function (array $data): bool {
                    return $data['service']['name'] === 'focus';
                },
            ],
            'process' => [
                function (array $data): bool {
                    return $data['process']['pid'] === 3;
                },
            ],
            'system' => [
                function (array $data): bool {
                    return $data['system']['hostname'] === 'foo.bar';
                },
            ],
            'transaction valid uuid' => [
                function (array $data): bool {
                    return Uuid::isValid($data['transactions'][0]['id']);
                },
            ],
            'transaction name contains method and url' => [
                function (array $data): bool {
                    $name = $data['transactions'][0]['name'];
                    Assert::assertContains('http://foo.bar', $name);
                    Assert::assertContains('GET', $name);

                    return true;
                },
            ],
            'transaction type' => [
                function (array $data): bool {
                    return $data['transactions'][0]['type'] === 'request';
                },
            ],
            'transaction span' => [
                function (array $data): bool {
                    return $data['transactions'][0]['spans'][0]['name'] === DummyHandler::SPAN_NAME;
                },
            ],
            'transaction mark' => [
                function (array $data): bool {
                    return $data['transactions'][0]['marks'][DummyHandler::MARK_NAME] === DummyHandler::MARK_VALUE;
                },
            ],
        ];
    }

    public function testPicksUpCorrectCorrelationIdFromHeader(): void
    {
        $id            = Uuid::uuid4();
        $ensuresHeader = function (Transaction $transaction) use ($id): bool {
            $data = $transaction->jsonSerialize();

            return $data['transactions'][0]['context']['tags']['correlation-id'] === $id->toString();
        };
        $this->client->sendTransactionAsync(Argument::that($ensuresHeader))
                     ->shouldBeCalled();
        $this->client->waitForResponses()
                     ->shouldBeCalled();

        $request = $this->request->withAddedHeader(HttpClientWrapper::CORRELATION_ID_HEADER, $id->toString());
        $this->middleware->process($request, $this->dummy);
    }

    public function testIgnoresIncorrectCorrelationIdFromHeader(): void
    {
        $request = $this->request->withAddedHeader(HttpClientWrapper::CORRELATION_ID_HEADER, 'no uuid');

        self::assertInstanceOf(ResponseInterface::class, $this->middleware->process($request, $this->dummy));
    }
}
