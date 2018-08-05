<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Dummy;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use TechDeCo\ElasticApmAgent\Convenience\Middleware\OpenTransactionRequestEnricher;
use TechDeCo\ElasticApmAgent\Convenience\Middleware\OpenTransactionResponseEnricher;
use TechDeCo\ElasticApmAgent\Convenience\OpenTransaction;
use TechDeCo\ElasticApmAgent\Message\Span;

final class DummyOpenTransactionRequestResponseEnricher implements OpenTransactionRequestEnricher, OpenTransactionResponseEnricher
{
    public function enrichFromRequest(OpenTransaction $transaction, RequestInterface $request): void
    {
        $transaction->addSpan(new Span(1.0, 'start', 0, 'test'));
    }

    public function enrichFromResponse(OpenTransaction $transaction, ResponseInterface $response): void
    {
        $transaction->addSpan(new Span(2.0, 'end', 1, 'test'));
    }
}
