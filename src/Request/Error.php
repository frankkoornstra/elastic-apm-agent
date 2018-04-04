<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Request;

use JsonSerializable;
use TechDeCo\ElasticApmAgent\Message\Error as ErrorMessage;
use TechDeCo\ElasticApmAgent\Message\Process;
use TechDeCo\ElasticApmAgent\Message\Service;
use TechDeCo\ElasticApmAgent\Message\System;
use TechDeCo\ElasticApmAgent\Serialization;

final class Error implements JsonSerializable
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
     * @var ErrorMessage[]
     */
    private $errorList = [];

    /**
     * @var System
     */
    private $system;

    public function __construct(Service $service, ErrorMessage ...$error)
    {
        $this->service   = $service;
        $this->errorList = $error;
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
            'errors' => Serialization::serialize(...$this->errorList),
            'system' => $this->system ? $this->system->jsonSerialize() : null,
        ]);
    }
}
