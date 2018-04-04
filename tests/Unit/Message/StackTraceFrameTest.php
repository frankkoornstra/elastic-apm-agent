<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Message;

use PHPUnit\Framework\TestCase;
use TechDeCo\ElasticApmAgent\Message\StackTraceFrame;

final class StackTraceFrameTest extends TestCase
{
    public function testAll(): void
    {
        $actual   = (new StackTraceFrame('alloy.txt', 5))
            ->atPath('/root/alloy.txt')
            ->atColumnNumber(3)
            ->withLineContext('$bla = 1')
            ->inFunction('createBeast')
            ->originatingFromLibraryCode()
            ->inModule('zeta')
            ->withPostContext('$baz = 3')
            ->withPreContext('$foo = 2')
            ->jsonSerialize();
        $expected = [
            'abs_path' => '/root/alloy.txt',
            'colno' => 3,
            'context_line' => '$bla = 1',
            'filename' => 'alloy.txt',
            'function' => 'createBeast',
            'library_frame' => true,
            'lineno' => 5,
            'module' => 'zeta',
            'post_context' => ['$baz = 3'],
            'pre_context' => ['$foo = 2'],
        ];

        self::assertEquals($expected, $actual);
    }

    public function testOriginatingFromUser(): void
    {
        $actual = (new StackTraceFrame('alloy.txt', 5))->originatingFromUserCode()->jsonSerialize();

        self::assertFalse($actual['library_frame']);
    }

    public function testFiltersEmpty(): void
    {
        $actual   = (new StackTraceFrame('foo.txt', 3))->jsonSerialize();
        $expected = [
            'filename' => 'foo.txt',
            'lineno' => 3,
        ];

        self::assertEquals($expected, $actual);
    }
}
