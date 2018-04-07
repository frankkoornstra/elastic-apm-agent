<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent;

use TechDeCo\ElasticApmAgent\Exception\ClientException;
use TechDeCo\ElasticApmAgent\Request\Error;
use TechDeCo\ElasticApmAgent\Request\Transaction;

interface Client
{
    /**
     * @throws ClientException
     */
    public function sendTransaction(Transaction $transaction): void;

    /**
     * @throws ClientException
     */
    public function sendError(Error $error): void;
}
