<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Message;

use PHPUnit\Framework\TestCase;
use TechDeCo\ElasticApmAgent\Message\Timestamp;

final class TimestampTest extends TestCase
{
    public function testWithUtc(): void
    {
        $date = new Timestamp('2018-01-15 12:00');

        self::assertEquals('2018-01-15T12:00:00.000000Z', $date->jsonSerialize());
    }
}
