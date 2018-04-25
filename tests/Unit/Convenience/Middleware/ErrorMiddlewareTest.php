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
use TechDeCo\ElasticApmAgent\Client;
use TechDeCo\ElasticApmAgent\Convenience\Middleware\ErrorMiddleware;
use TechDeCo\ElasticApmAgent\Convenience\Middleware\TransactionMiddleware;
use TechDeCo\ElasticApmAgent\Convenience\OpenTransaction;
use TechDeCo\ElasticApmAgent\Message\Process;
use TechDeCo\ElasticApmAgent\Message\Service;
use TechDeCo\ElasticApmAgent\Message\System;
use TechDeCo\ElasticApmAgent\Message\Timestamp;
use TechDeCo\ElasticApmAgent\Message\VersionedName;
use TechDeCo\ElasticApmAgent\Request\Error;
use TechDeCo\ElasticApmAgent\Tests\Dummy\DummyHandler;
use function strtoupper;

final class ErrorMiddlewareTest extends TestCase
{
    private const TRANSACTION_ID = '46A3949C-BCF8-449F-9573-7E988983A87E';

    /**
     * @var DummyHandler
     */
    private $dummy;

    /**
     * @var OpenTransaction
     */
    private $transaction;

    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * @var Client|ObjectProphecy
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
        $this->dummy       = new DummyHandler(0, true);
        $this->transaction = new OpenTransaction(
            Uuid::fromString(self::TRANSACTION_ID),
            'test',
            new Timestamp(),
            'test-type'
        );
        $this->request     = (new ServerRequest('GET', 'http://foo.bar'))
            ->withAttribute(TransactionMiddleware::TRANSACTION_ATTRIBUTE, $this->transaction);
        $this->client      = $this->prophesize(Client::class);
        $this->service     = new Service(new VersionedName('alloy', '1'), 'focus');
        $this->process     = new Process(3);
        $this->system      = (new System())->atHost('foo.bar');
        $this->middleware  = new ErrorMiddleware(
            $this->client->reveal(),
            $this->service,
            $this->process,
            $this->system
        );
    }

    public function testReturnsResponseWhenNoException(): void
    {
        $this->client->sendError(Argument::any())->shouldNotBeCalled();
        $dummy = new DummyHandler(0);

        self::assertInstanceOf(ResponseInterface::class, $this->middleware->process($this->request, $dummy));
    }

    public function testSendsErrorForResponses(): void
    {
        $this->expectException(\Throwable::class);

        $this->client->sendError(Argument::type(Error::class))
                     ->shouldBeCalled();

        $this->middleware->process($this->request, $this->dummy);
    }

    /**
     * @dataProvider provideErrorAssertions
     */
    public function testSentErrorCaptures(callable $ensuresData): void
    {
        $this->expectException(\Throwable::class);

        $packedEnsuresData = function (Error $error) use ($ensuresData): bool {
            $data = $error->jsonSerialize();

            return $ensuresData($data);
        };
        $this->client->sendError(Argument::that($packedEnsuresData))
                     ->shouldBeCalled();

        $this->middleware->process($this->request, $this->dummy);
    }

    /**
     * @return callable[][]
     */
    public function provideErrorAssertions(): array
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
            'error exception message' => [
                function (array $data): bool {
                    return $data['errors'][0]['exception']['message'] === DummyHandler::EXCEPTION_MESSAGE;
                },
            ],
            'error exception code' => [
                function (array $data): bool {
                    return $data['errors'][0]['exception']['code'] === DummyHandler::EXCEPTION_CODE;
                },
            ],
            'error exception stacktrace present' => [
                function (array $data): bool {
                    $stacktrace = $data['errors'][0]['exception']['stacktrace'];
                    Assert::assertInternalType('array', $stacktrace);
                    Assert::assertNotEmpty($stacktrace);

                    return true;
                },
            ],
            'error exception type' => [
                function (array $data): bool {
                    Assert::assertContains('Exception', $data['errors'][0]['exception']['type']);

                    return true;
                },
            ],
            'error valid uuid' => [
                function (array $data): bool {
                    return Uuid::isValid($data['errors'][0]['id']);
                },
            ],
            'error context request method' => [
                function (array $data): bool {
                    return $data['errors'][0]['context']['request']['method'] === 'GET';
                },
            ],
            'error context request uri' => [
                function (array $data): bool {
                    return $data['errors'][0]['context']['request']['url']['raw'] === 'http://foo.bar';
                },
            ],
            'error context request protocol version' => [
                function (array $data): bool {
                    return $data['errors'][0]['context']['request']['http_version'] === '1.1';
                },
            ],
            'error transaction correlation' => [
                function (array $data): bool {
                    return strtoupper($data['errors'][0]['transaction']) === self::TRANSACTION_ID;
                },
            ],
        ];
    }
}
