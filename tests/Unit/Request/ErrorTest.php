<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Request;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use TechDeCo\ElasticApmAgent\Message\Error as ErrorMessage;
use TechDeCo\ElasticApmAgent\Message\Log;
use TechDeCo\ElasticApmAgent\Message\Process;
use TechDeCo\ElasticApmAgent\Message\Service;
use TechDeCo\ElasticApmAgent\Message\System;
use TechDeCo\ElasticApmAgent\Message\VersionedName;
use TechDeCo\ElasticApmAgent\Request\Error;

final class ErrorTest extends TestCase
{
    public function testAll(): void
    {
        $agent   = new VersionedName('thunderjaw', '1.0');
        $service = new Service($agent, 'rockbreaker');
        $process = new Process(213);
        $system  = (new System())->atHost('hades');
        $date    = new DateTimeImmutable('2018-02-14T10:11:12.131+01:00');
        $log     = new Log('blabla');
        $message = ErrorMessage::fromLog($log, $date);

        $actual = (new Error($service, $message))
            ->inProcess($process)
            ->onSystem($system)
            ->jsonSerialize();

        $expected = [
            'service' => [
                'agent' => [
                    'name' => 'thunderjaw',
                    'version' => '1.0',
                ],
                'name' => 'rockbreaker',
            ],
            'process' => [
                'pid' => 213,
            ],
            'system' => [
                'hostname' => 'hades',
            ],
            'errors' => [
                [
                    'log' => [
                        'message' => 'blabla',
                    ],
                    'timestamp' => '2018-02-14T10:11:12.131+01:00',
                ],
            ],
        ];

        self::assertEquals($expected, $actual);
    }

    public function testFiltersEmpty(): void
    {
        $agent   = new VersionedName('thunderjaw', '1.0');
        $service = new Service($agent, 'rockbreaker');
        $date    = new DateTimeImmutable('2018-02-14T10:11:12.131+01:00');
        $log     = new Log('blabla');
        $message = ErrorMessage::fromLog($log, $date);

        $actual = (new Error($service, $message))
            ->jsonSerialize();

        $expected = [
            'service' => [
                'agent' => [
                    'name' => 'thunderjaw',
                    'version' => '1.0',
                ],
                'name' => 'rockbreaker',
            ],
            'errors' => [
                [
                    'log' => [
                        'message' => 'blabla',
                    ],
                    'timestamp' => '2018-02-14T10:11:12.131+01:00',
                ],
            ],
        ];

        self::assertEquals($expected, $actual);
    }
}
