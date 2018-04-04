<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Message;

use PHPUnit\Framework\TestCase;
use TechDeCo\ElasticApmAgent\Message\VersionedName;

final class VersionedNameTest extends TestCase
{
    public function testAll(): void
    {
        $actual   = (new VersionedName('alloy', '1.0'))->jsonSerialize();
        $expected = [
            'name' => 'alloy',
            'version' => '1.0',
        ];

        self::assertEquals($expected, $actual);
    }
}
