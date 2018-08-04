<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Convenience\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TechDeCo\ElasticApmAgent\Convenience\OpenTransaction;
use function assert;

final class OpenTransactionRequestEnrichmentMiddleware implements MiddlewareInterface
{
    /**
     * @var OpenTransactionRequestEnricher[]
     */
    private $enricherList;

    public function __construct(OpenTransactionRequestEnricher ...$enricherList)
    {
        $this->enricherList = $enricherList;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $transaction = $request->getAttribute(TransactionMiddleware::TRANSACTION_ATTRIBUTE);
        assert($transaction instanceof OpenTransaction, 'Expected a transaction in the request attributes');

        foreach ($this->enricherList as $enricher) {
            $enricher->enrichFromRequest($transaction, $request);
        }

        return $handler->handle($request);
    }
}
