<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Message;

use PHPUnit\Framework\TestCase;
use TechDeCo\ElasticApmAgent\Message\Timestamp;
use function date_default_timezone_get;
use function date_default_timezone_set;

final class TimestampTest extends TestCase
{
    /**
     * Used to restore correct timezone after switching to different ones during tests.
     * @var $oldTimeZone string
     */
    private $oldTimeZone;

    public function setUp(): void
    {
        $this->oldTimeZone = date_default_timezone_get();
    }

    public function testWithUtc(): void
    {
        date_default_timezone_set('UTC');
        $date = new Timestamp('2018-01-15 12:00');

        self::assertEquals('2018-01-15T12:00:00.000000Z', $date->jsonSerialize());
    }

    public function testWithOtherTimezone(): void
    {
        date_default_timezone_set('Asia/Tokyo');
        $date = new Timestamp('2018-01-15 12:00');

        self::assertEquals('2018-01-15T03:00:00.000000Z', $date->jsonSerialize());
    }

    public function tearDown(): void
    {
        date_default_timezone_set($this->oldTimeZone);
    }
}
