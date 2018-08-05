<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Acceptance;

use Behat\Behat\Context\Context;
use Error;
use GuzzleHttp\Psr7\ServerRequest;
use Northwoods\Broker\Broker;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TechDeCo\ElasticApmAgent\Client\HttplugAsyncClient;
use TechDeCo\ElasticApmAgent\Convenience\Middleware\ErrorMiddleware;
use TechDeCo\ElasticApmAgent\Convenience\Middleware\OpenTransactionRequestEnrichmentMiddleware;
use TechDeCo\ElasticApmAgent\Convenience\Middleware\OpenTransactionResponseEnrichmentMiddleware;
use TechDeCo\ElasticApmAgent\Convenience\Middleware\TransactionMiddleware;
use TechDeCo\ElasticApmAgent\Convenience\OpenTransaction;
use TechDeCo\ElasticApmAgent\Exception\ClientException;
use TechDeCo\ElasticApmAgent\Message\Process;
use TechDeCo\ElasticApmAgent\Message\Service;
use TechDeCo\ElasticApmAgent\Message\System;
use TechDeCo\ElasticApmAgent\Message\VersionedName;
use TechDeCo\ElasticApmAgent\Tests\Dummy\DummyHandler;
use TechDeCo\ElasticApmAgent\Tests\Dummy\DummyOpenTransactionRequestResponseEnricher;
use Throwable;
use function assert;

final class MiddlewareContext implements Context
{
    /**
     * @var HttplugAsyncClient
     */
    private $client;

    /**
     * @var RequestHandlerInterface
     */
    private $normalHandler;

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
     * @var Broker
     */
    private $stack;

    /**
     * @var Throwable
     */
    private $throwable;

    /**
     * @var RequestHandlerInterface
     */
    private $exceptionHandler;

    public function __construct(
        HttplugAsyncClient $client,
        RequestHandlerInterface $normalHandler,
        RequestHandlerInterface $exceptionHandler
    ) {
        $this->client           = $client;
        $this->normalHandler    = $normalHandler;
        $this->exceptionHandler = $exceptionHandler;
        $this->stack            = new Broker();
        $this->service          = new Service(
            new VersionedName('focus', '1'),
            'alloy'
        );
        $this->process          = new Process(3);
        $this->system           = new System();
    }

    /**
     * @Given I add the transaction middleware to my stack
     */
    public function iAddTheTransactionMiddlewareToMyStack(): void
    {
        $this->stack = $this->stack->always([
            new TransactionMiddleware(
                $this->client,
                $this->service,
                $this->process,
                $this->system
            ),
        ]);
    }

    /**
     * @Given I add the error middleware to my stack
     */
    public function iAddTheErrorMiddlewareToMyStack(): void
    {
        $this->stack = $this->stack->always([
            new ErrorMiddleware(
                $this->client,
                $this->service,
                $this->process,
                $this->system
            ),
        ]);
    }

    /**
     * @Given I add the open transaction request enrichment middleware to my stack
     */
    public function iAddTheOpenTransactionRequestEnrichmentMiddlewareToMyStack(): void
    {
        $this->stack = $this->stack->always([
            new OpenTransactionRequestEnrichmentMiddleware(
                new DummyOpenTransactionRequestResponseEnricher()
            ),
        ]);
    }

    /**
     * @Given I add the open transaction response enrichment middleware to my stack
     */
    public function iAddTheOpenTransactionResponseEnrichmentMiddlewareToMyStack(): void
    {
        $this->stack = $this->stack->always([
            new OpenTransactionResponseEnrichmentMiddleware(
                new DummyOpenTransactionRequestResponseEnricher()
            ),
        ]);
    }

    /**
     * @When I send the default server request
     */
    public function iSendTheDefaultServerRequest(): void
    {
        $this->handle($this->normalHandler);
    }

    /**
     * @When I send a server request that throws an exception
     */
    public function iSendAServerRequestThatThrowsAnException(): void
    {
        $this->handle($this->exceptionHandler);
    }

    private function handle(RequestHandlerInterface $handler): void
    {
        $request        = new ServerRequest('GET', 'http://gaia.prime');
        $handlerWrapper = function (ServerRequestInterface $request) use ($handler) {
            return $handler->handle($request);
        };

        try {
            $this->stack->handle($request, $handlerWrapper);
            $this->client->waitForResponses();
        } catch (Throwable $e) {
            $this->throwable = $e;
        }
    }

    /**
     * @Then the transaction sent by middleware is accepted
     */
    public function theTransactionSentByMiddlewareIsAccepted(): void
    {
        Assert::assertEmpty($this->throwable);
    }

    /**
     * @Then the error sent by the middleware is accepted
     */
    public function theErrorSentByTheMiddlewareIsAccepted(): void
    {
        Assert::assertNotEmpty($this->throwable);
        Assert::assertNotInstanceOf(ClientException::class, $this->throwable);
        Assert::assertNotInstanceOf(Error::class, $this->throwable);
    }

    /**
     * @Then the open transaction is enriched with request data
     * @throws AssertionFailedError
     */
    public function theOpenTransactionIsEnrichedWithRequestData(): void
    {
        assert($this->normalHandler instanceof DummyHandler);

        $transaction = $this->normalHandler->request->getAttribute(TransactionMiddleware::TRANSACTION_ATTRIBUTE);
        assert($transaction instanceof OpenTransaction);

        $json = $transaction->toTransaction()->jsonSerialize();
        $this->assertSpanWithTypeAndName($json, 'test', 'start');
    }

    /**
     * @Then the open transaction is enriched with response data
     * @throws AssertionFailedError
     */
    public function theOpenTransactionIsEnrichedWithResponseData(): void
    {
        assert($this->normalHandler instanceof DummyHandler);

        $transaction = $this->normalHandler->request->getAttribute(TransactionMiddleware::TRANSACTION_ATTRIBUTE);
        assert($transaction instanceof OpenTransaction);

        $json = $transaction->toTransaction()->jsonSerialize();
        $this->assertSpanWithTypeAndName($json, 'test', 'end');
    }

    /**
     * @param mixed[] $json
     * @throws AssertionFailedError
     */
    private function assertSpanWithTypeAndName(array $json, string $type, string $name): void
    {
        foreach ($json['spans'] as $span) {
            if ($span['type'] === $type && $span['name'] === $name) {
                return;
            }
        }

        Assert::fail('No span found that matches request data');
    }
}
