<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Message;

use PHPUnit\Framework\TestCase;
use TechDeCo\ElasticApmAgent\Message\Log;
use TechDeCo\ElasticApmAgent\Message\StackTraceFrame;

final class LogTest extends TestCase
{
    public function testAll(): void
    {
        $frame  = new StackTraceFrame('foo.txt', 16);
        $actual = (new Log('blabla'))
            ->withSeverityLevel('debug')
            ->byLoggerNamed('alloy')
            ->withParameterizedMessage('bla$bla')
            ->withStackTraceFrame($frame)
            ->jsonSerialize();

        $expected = [
            'level' => 'debug',
            'logger_name' => 'alloy',
            'message' => 'blabla',
            'param_message' => 'bla$bla',
            'stacktrace' => [
                [
                    'filename' => 'foo.txt',
                    'lineno' => 16,
                ],
            ],
        ];

        self::assertEquals($expected, $actual);
    }

    public function testFiltersEmpty(): void
    {
        $actual = (new Log('blabla'))->jsonSerialize();

        $expected = ['message' => 'blabla'];

        self::assertEquals($expected, $actual);
    }
}
