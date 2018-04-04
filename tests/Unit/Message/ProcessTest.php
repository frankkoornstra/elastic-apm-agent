<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Message;

use PHPUnit\Framework\TestCase;
use TechDeCo\ElasticApmAgent\Message\Process;

final class ProcessTest extends TestCase
{
    public function testAll(): void
    {
        $actual = (new Process(103))
            ->withParentProcessId(5)
            ->titled('foo.sh')
            ->withArguments('bla', 'bloo')
            ->jsonSerialize();

        $expected = [
            'pid' => 103,
            'ppid' => 5,
            'title' => 'foo.sh',
            'argv' => [
                'bla',
                'bloo',
            ],
        ];

        self::assertEquals($expected, $actual);
    }

    public function testFiltersEmpty(): void
    {
        $actual = (new Process(103))->jsonSerialize();

        $expected = ['pid' => 103];

        self::assertEquals($expected, $actual);
    }
}
