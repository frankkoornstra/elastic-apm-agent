<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Message;

use PHPUnit\Framework\TestCase;
use TechDeCo\ElasticApmAgent\Message\Span;
use TechDeCo\ElasticApmAgent\Message\StackTraceFrame;

final class SpanTest extends TestCase
{
    public function testAll(): void
    {
        $parent = (new Span(0.0, 'parent', 0.0, 'parent'))->withId(9);
        $frame  = (new StackTraceFrame('behemoth.txt', 2));
        $actual = (new Span(15.3, 'alloy', 0.0, 'nora'))
            ->withId(3)
            ->withContext('spear', true)
            ->withParent($parent)
            ->withStackTraceFrame($frame)
            ->jsonSerialize();

        $expected = [
            'id' => 3,
            'context' => ['spear' => true],
            'duration' => 15.3,
            'name' => 'alloy',
            'parent' => 9,
            'stacktrace' => [
                [
                    'filename' => 'behemoth.txt',
                    'lineno' => 2,
                ],
            ],
            'start' => 0.0,
            'type' => 'nora',
        ];

        self::assertEquals($expected, $actual);
    }

    public function testFiltersEmpty(): void
    {
        $actual   = (new Span(15.3, 'alloy', 0.0, 'nora'))->jsonSerialize();
        $expected = [
            'duration' => 15.3,
            'name' => 'alloy',
            'start' => 0.0,
            'type' => 'nora',
        ];

        self::assertEquals($expected, $actual);
    }
}
