<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Request;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use TechDeCo\ElasticApmAgent\Message\Process;
use TechDeCo\ElasticApmAgent\Message\Service;
use TechDeCo\ElasticApmAgent\Message\System;
use TechDeCo\ElasticApmAgent\Message\Transaction as TransactionMessage;
use TechDeCo\ElasticApmAgent\Message\VersionedName;
use TechDeCo\ElasticApmAgent\Request\Transaction;

final class TransactionTest extends TestCase
{
    public function testAll(): void
    {
        $id      = Uuid::uuid4();
        $date    = new DateTimeImmutable('2018-02-14T10:11:12.131+01:00');
        $message = (new TransactionMessage(13.2, $id, 'alloy', $date, 'zeta'));
        $agent   = new VersionedName('thunderjaw', '1.0');
        $service = new Service($agent, 'rockbreaker');
        $process = new Process(213);
        $system  = (new System())->atHost('hades');

        $actual = (new Transaction($service, $message))
            ->inProcess($process)
            ->onSystem($system)
            ->jsonSerialize();

        $expected = [
            'service' => [
                'agent' => [
                    'name' => 'thunderjaw',
                    'version' => '1.0'
                ],
                'name' => 'rockbreaker'
            ],
            'process' => [
                'pid' => 213,
            ],
            'system' => [
                'hostname' => 'hades',
            ],
            'transactions' => [
                [
                    'duration' => 13.2,
                    'id' => (string) $id,
                    'name' => 'alloy',
                    'timestamp' => '2018-02-14T10:11:12.131+01:00',
                    'type' => 'zeta',
                ],
            ],
        ];

        self::assertEquals($expected, $actual);
    }

    public function testFiltersEmpty(): void
    {
        $id      = Uuid::uuid4();
        $date    = new DateTimeImmutable('2018-02-14T10:11:12.131+01:00');
        $message = (new TransactionMessage(13.2, $id, 'alloy', $date, 'zeta'));
        $agent   = new VersionedName('thunderjaw', '1.0');
        $service = new Service($agent, 'rockbreaker');

        $actual = (new Transaction($service, $message))
            ->jsonSerialize();

        $expected = [
            'service' => [
                'agent' => [
                    'name' => 'thunderjaw',
                    'version' => '1.0'
                ],
                'name' => 'rockbreaker'
            ],
            'transactions' => [
                [
                    'duration' => 13.2,
                    'id' => (string) $id,
                    'name' => 'alloy',
                    'timestamp' => '2018-02-14T10:11:12.131+01:00',
                    'type' => 'zeta',
                ],
            ],
        ];

        self::assertEquals($expected, $actual);
    }
}
