<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Convenience\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use TechDeCo\ElasticApmAgent\Client;
use TechDeCo\ElasticApmAgent\Message\Process;
use TechDeCo\ElasticApmAgent\Message\Service;
use TechDeCo\ElasticApmAgent\Message\System;

abstract class Middleware implements MiddlewareInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Service
     */
    protected $service;

    /**
     * @var Process
     */
    protected $process;

    /**
     * @var System
     */
    protected $system;

    public function __construct(Client $client, Service $service, Process $process, System $system)
    {
        $this->client  = $client;
        $this->service = $service;
        $this->process = $process;
        $this->system  = $system;
    }
}
