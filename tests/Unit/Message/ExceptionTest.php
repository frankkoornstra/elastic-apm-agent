<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Message;

use PHPUnit\Framework\TestCase;
use TechDeCo\ElasticApmAgent\Message\Exception;
use TechDeCo\ElasticApmAgent\Message\StackTraceFrame;

final class ExceptionTest extends TestCase
{
    public function testAll(): void
    {
        $frame  = new StackTraceFrame('error.txt', 16);
        $actual = (new Exception('blabla'))
            ->withCode(517)
            ->inModule('zeta')
            ->withAttribute('name', 'alloy')
            ->withStackTraceFrame($frame)
            ->asType('BlooException')
            ->thatIsHandled()
            ->jsonSerialize();

        $expected = [
            'code' => 517,
            'message' => 'blabla',
            'module' => 'zeta',
            'attributes' => ['name' => 'alloy'],
            'stacktrace' => [
                [
                    'filename' => 'error.txt',
                    'lineno' => 16,
                ],
            ],
            'type' => 'BlooException',
            'handled' => true,
        ];

        self::assertEquals($expected, $actual);
    }

    public function testIsNotHandled(): void
    {
        $actual = (new Exception('blabla'))->thatIsNotHandled()->jsonSerialize();

        $expected = [
            'message' => 'blabla',
            'handled' => false,
        ];

        self::assertEquals($expected, $actual);
    }

    public function testFiltersEmpty(): void
    {
        $actual = (new Exception('blabla'))->jsonSerialize();

        $expected = ['message' => 'blabla'];

        self::assertEquals($expected, $actual);
    }
}
