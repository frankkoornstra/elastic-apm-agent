<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Convenience\Middleware;

use Psr\Http\Message\RequestInterface;
use TechDeCo\ElasticApmAgent\Convenience\OpenTransaction;

interface OpenTransactionRequestEnricher
{
    public function enrichFromRequest(OpenTransaction $transaction, RequestInterface $request): void;
}
