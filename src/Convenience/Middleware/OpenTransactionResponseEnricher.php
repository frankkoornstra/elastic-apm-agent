<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Convenience\Middleware;

use Psr\Http\Message\ResponseInterface;
use TechDeCo\ElasticApmAgent\Convenience\OpenTransaction;

interface OpenTransactionResponseEnricher
{
    public function enrichFromResponse(OpenTransaction $transaction, ResponseInterface $response): void;
}
