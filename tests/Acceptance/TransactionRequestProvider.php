<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Acceptance;

use TechDeCo\ElasticApmAgent\Request\Transaction;

interface TransactionRequestProvider
{
    public function createRequest(): Transaction;
}
