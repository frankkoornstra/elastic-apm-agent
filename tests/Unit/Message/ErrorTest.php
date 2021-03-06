<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Message;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use TechDeCo\ElasticApmAgent\Message\Context;
use TechDeCo\ElasticApmAgent\Message\Error;
use TechDeCo\ElasticApmAgent\Message\Exception;
use TechDeCo\ElasticApmAgent\Message\Log;
use TechDeCo\ElasticApmAgent\Message\Timestamp;

final class ErrorTest extends TestCase
{
    public function testAllByFromException(): void
    {
        $exception     = new Exception('blabla');
        $date          = new Timestamp('2018-01-01T10:11:12.131');
        $context       = (new Context())->withTag('name', 'alloy');
        $id            = Uuid::uuid4();
        $log           = new Log('bloo');
        $transactionId = Uuid::uuid4();

        $actual = Error::fromException($exception, $date)
                       ->inContext($context)
                       ->withCulprit('hades')
                       ->withId($id)
                       ->withLog($log)
                       ->correlatedToTransactionId($transactionId)
                       ->jsonSerialize();

        $expected = [
            'context' => [
                'tags' => ['name' => 'alloy'],
            ],
            'culprit' => 'hades',
            'exception' => ['message' => 'blabla'],
            'id' => $id->toString(),
            'log' => ['message' => 'bloo'],
            'timestamp' => '2018-01-01T10:11:12.131000Z',
            'transaction' => $transactionId->toString(),
        ];

        self::assertEquals($expected, $actual);
    }

    public function testUnusedByFromLog(): void
    {
        $log       = new Log('bloo');
        $exception = new Exception('blabla');
        $date      = new Timestamp('2018-01-01T10:11:12.131');

        $actual = Error::fromLog($log, $date)
                       ->causedByException($exception)
                       ->jsonSerialize();

        $expected = [
            'exception' => ['message' => 'blabla'],
            'log' => ['message' => 'bloo'],
            'timestamp' => '2018-01-01T10:11:12.131000Z',
        ];

        self::assertEquals($expected, $actual);
    }

    public function testFiltersEmpty(): void
    {
        $log  = new Log('bloo');
        $date = new Timestamp('2018-01-01T10:11:12.131');

        $actual = Error::fromLog($log, $date)
                       ->jsonSerialize();

        $expected = [
            'log' => ['message' => 'bloo'],
            'timestamp' => '2018-01-01T10:11:12.131000Z',
        ];

        self::assertEquals($expected, $actual);
    }
}
