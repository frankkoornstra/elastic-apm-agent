<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Request;

use JsonSerializable;
use TechDeCo\ElasticApmAgent\Message\Process;
use TechDeCo\ElasticApmAgent\Message\Service;
use TechDeCo\ElasticApmAgent\Message\System;
use TechDeCo\ElasticApmAgent\Message\Transaction as TransactionMessage;
use TechDeCo\ElasticApmAgent\Serialization;

final class Transaction implements JsonSerializable
{
    /**
     * @var Service
     */
    private $service;

    /**
     * @var Process|null
     */
    private $process;

    /**
     * @var TransactionMessage[]
     */
    private $transactionList = [];

    /**
     * @var System
     */
    private $system;

    public function __construct(Service $service, TransactionMessage ...$transaction)
    {
        $this->service         = $service;
        $this->transactionList = $transaction;
    }

    public function inProcess(Process $process): self
    {
        $me          = clone $this;
        $me->process = $process;

        return $me;
    }

    public function onSystem(System $system): self
    {
        $me         = clone $this;
        $me->system = $system;

        return $me;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        return Serialization::filterUnset([
            'service' => $this->service->jsonSerialize(),
            'process' => $this->process ? $this->process->jsonSerialize() : null,
            'system' => $this->system ? $this->system->jsonSerialize() : null,
            'transactions' => Serialization::serialize(...$this->transactionList),
        ]);
    }
}
