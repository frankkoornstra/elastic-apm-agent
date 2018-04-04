<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Message;

use PHPUnit\Framework\TestCase;
use TechDeCo\ElasticApmAgent\Message\System;

final class SystemTest extends TestCase
{
    public function testAll(): void
    {
        $actual   = (new System())
            ->onArchitecture('x86')
            ->atHost('foo.bar')
            ->onPlatform('ubuntu')
            ->jsonSerialize();
        $expected = [
            'hostname' => 'foo.bar',
            'architecture' => 'x86',
            'platform' => 'ubuntu',
        ];

        self::assertEquals($expected, $actual);
    }

    public function testFiltersEmpty(): void
    {
        $actual = (new System())->jsonSerialize();

        self::assertEmpty($actual);
    }
}
