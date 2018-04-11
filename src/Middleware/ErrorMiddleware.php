<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;
use TechDeCo\ElasticApmAgent\AsyncClient;
use TechDeCo\ElasticApmAgent\Exception\ClientException;
use TechDeCo\ElasticApmAgent\Message\Context;
use TechDeCo\ElasticApmAgent\Message\Error as ErrorMessage;
use TechDeCo\ElasticApmAgent\Message\Exception;
use TechDeCo\ElasticApmAgent\Message\Process;
use TechDeCo\ElasticApmAgent\Message\Request;
use TechDeCo\ElasticApmAgent\Message\Service;
use TechDeCo\ElasticApmAgent\Message\StackTraceFrame;
use TechDeCo\ElasticApmAgent\Message\System;
use TechDeCo\ElasticApmAgent\Message\Timestamp;
use TechDeCo\ElasticApmAgent\Message\Url;
use TechDeCo\ElasticApmAgent\Request\Error as ErrorRequest;
use Throwable;
use function array_filter;
use function array_map;
use function get_class;
use function implode;

final class ErrorMiddleware extends Middleware
{
    /**
     * @var Context
     */
    private $context;

    public function __construct(
        AsyncClient $client,
        Service $service,
        Process $process,
        System $system,
        ?Context $context = null
    ) {
        parent::__construct($client, $service, $process, $system);

        $this->context = $context ?? new Context();
    }

    /**
     * @throws ClientException
     * @throws Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $t) {
            $message = $this->createMessage($request, $t);
            $request = (new ErrorRequest($this->service, $message))
                ->onSystem($this->system)
                ->inProcess($this->process);

            $this->client->sendErrorAsync($request);
            $this->client->waitForResponses();

            throw $t;
        }
    }

    private function createMessage(ServerRequestInterface $request, Throwable $throwable): ErrorMessage
    {
        $message = ErrorMessage::fromException($this->createException($throwable), new Timestamp())
                               ->withId(Uuid::uuid4())
                               ->inContext($this->createContext($request));

        /** @var ?OpenTransaction $transaction */
        $transaction = $request->getAttribute(TransactionMiddleware::TRANSACTION_ATTRIBUTE);
        if ($transaction !== null) {
            $message = $message->correlatedToTransactionId($transaction->getId());
        }

        return $message;
    }

    private function createException(Throwable $throwable): Exception
    {
        return (new Exception($throwable->getMessage()))
            ->withCode($throwable->getCode())
            ->withStackTraceFrame(...$this->createStackTrace($throwable))
            ->asType(get_class($throwable));
    }

    /**
     * @return StackTraceFrame[]
     */
    private function createStackTrace(Throwable $throwable): array
    {
        return array_map(
            function (array $frame): StackTraceFrame {
                $function = implode('::', array_filter([
                    $frame['class'] ?? null,
                    $frame['function'] ?? null,
                ]));

                return (new StackTraceFrame(
                    $frame['file'] ?? '<undefined>',
                    $frame['line'] ?? 0
                ))
                    ->inFunction($function);
            },
            $throwable->getTrace()
        );
    }

    private function createContext(ServerRequestInterface $request): Context
    {
        $request = (new Request(
            $request->getMethod(),
            Url::fromUri($request->getUri())
        ))
            ->onHttpVersion($request->getProtocolVersion());

        return $this->context->withRequest($request);
    }
}
