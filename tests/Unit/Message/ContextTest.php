<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Message;

use PHPUnit\Framework\TestCase;
use TechDeCo\ElasticApmAgent\Message\Context;
use TechDeCo\ElasticApmAgent\Message\Response;
use TechDeCo\ElasticApmAgent\Message\User;

final class ContextTest extends TestCase
{
    public function testAll(): void
    {
        $actual = (new Context())
            ->withCustomVariable('a', 'behemoth')
            ->withResponse((new Response())->resultingInStatusCode(200))
            ->withTag('b', 'charger')
            ->withUser((new User())->withId(9))
            ->jsonSerialize();

        $expected = [
            'custom' => ['a' => 'behemoth'],
            'response' => ['status_code' => 200],
            'user' => ['id' => 9],
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
