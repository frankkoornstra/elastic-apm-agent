<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Message;

use PHPUnit\Framework\TestCase;
use TechDeCo\ElasticApmAgent\Message\User;

final class UserTest extends TestCase
{
    public function testAll(): void
    {
        $actual   = (new User())
            ->withId('alloy')
            ->withEmail('alloy@hzd.nl')
            ->withUsername('Alloy')
            ->jsonSerialize();
        $expected = [
            'id' => 'alloy',
            'email' => 'alloy@hzd.nl',
            'username' => 'Alloy',
        ];

        self::assertEquals($expected, $actual);
    }

    public function testFiltersEmpty(): void
    {
        $actual = (new User())->jsonSerialize();

        self::assertEmpty($actual);
    }
}
