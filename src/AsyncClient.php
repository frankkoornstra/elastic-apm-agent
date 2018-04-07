<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent;

use TechDeCo\ElasticApmAgent\Exception\ClientException;
use TechDeCo\ElasticApmAgent\Request\Error;
use TechDeCo\ElasticApmAgent\Request\Transaction;

interface AsyncClient
{
    /**
     * @throws ClientException
     */
    public function sendTransactionAsync(Transaction $transaction): void;

    /**
     * @throws ClientException
     */
    public function sendErrorAsync(Error $error): void;

    /**
     * @throws ClientException
     */
    public function waitForResponses(): void;
}
