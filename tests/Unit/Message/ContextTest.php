<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Message;

use PHPUnit\Framework\TestCase;
use TechDeCo\ElasticApmAgent\Message\Context;

final class ContextTest extends TestCase
{
    public function testAll(): void
    {
        $actual = (new Context())
            ->withCustomVariable('a', 'behemoth')
            ->withTag('b', 'charger')
            ->jsonSerialize();

        $expected = [
            'custom' => ['a' => 'behemoth'],
            'tags' => ['b' => 'charger'],
        ];

        self::assertEquals($expected, $actual);
    }

    public function testFiltersEmpty(): void
    {
        $actual = (new Context())->jsonSerialize();

        self::assertEmpty($actual);
    }
}
