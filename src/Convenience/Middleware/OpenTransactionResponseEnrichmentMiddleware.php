<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Convenience\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TechDeCo\ElasticApmAgent\Convenience\OpenTransaction;
use function assert;

final class OpenTransactionResponseEnrichmentMiddleware implements MiddlewareInterface
{
    /**
     * @var OpenTransactionResponseEnricher[]
     */
    private $capturerList;

    public function __construct(OpenTransactionResponseEnricher ...$captureList)
    {
        $this->capturerList = $captureList;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $transaction = $request->getAttribute(TransactionMiddleware::TRANSACTION_ATTRIBUTE);
        assert($transaction instanceof OpenTransaction, 'Expected a transaction in the request attributes');

        $response = $handler->handle($request);

        foreach ($this->capturerList as $capturer) {
            $capturer->enrichFromResponse($transaction, $response);
        }

        return $response;
    }
}
