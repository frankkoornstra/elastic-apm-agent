<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Acceptance;

use Behat\Behat\Context\Context;
use GuzzleHttp\Psr7\ServerRequest;
use Northwoods\Broker\Broker;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TechDeCo\ElasticApmAgent\AsyncClient;
use TechDeCo\ElasticApmAgent\Convenience\Middleware\TransactionMiddleware;
use TechDeCo\ElasticApmAgent\Message\Process;
use TechDeCo\ElasticApmAgent\Message\Service;
use TechDeCo\ElasticApmAgent\Message\System;
use TechDeCo\ElasticApmAgent\Message\VersionedName;
use Throwable;

final class MiddlewareContext implements Context
{
    /**
     * @var AsyncClient
     */
    private $client;

    /**
     * @var RequestHandlerInterface
     */
    private $handler;

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

    public function __construct(AsyncClient $client, RequestHandlerInterface $handler)
    {
        $this->client  = $client;
        $this->handler = $handler;
        $this->stack   = new Broker();
        $this->service = new Service(
            new VersionedName('focus', '1'),
            'alloy'
        );
        $this->process = new Process(3);
        $this->system  = new System();
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
     * @When I send the default server request
     */
    public function iSendTheDefaultServerRequest(): void
    {
        $request = new ServerRequest('GET', 'http://gaia.prime');
        $handler = $this->handler;
        $handler = function (ServerRequestInterface $request) use ($handler) {
            return $handler->handle($request);
        };

        try {
            $this->stack->handle($request, $handler);
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
}
