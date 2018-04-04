<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Message;

use PHPUnit\Framework\TestCase;
use TechDeCo\ElasticApmAgent\Message\Socket;

final class SocketTest extends TestCase
{
    public function testAll(): void
    {
        $actual = (new Socket())
            ->thatIsEncrypted()
            ->fromRemoteAddress('1.2.3.4')
            ->jsonSerialize();

        $expected = [
            'remote_address' => '1.2.3.4',
            'encrypted' => true,
        ];

        self::assertEquals($expected, $actual);
    }

    public function testNotEncrypted(): void
    {
        $actual = (new Socket())->thatIsNotEncrypted()->jsonSerialize();

        self::assertFalse($actual['encrypted']);
    }

    public function testFiltersEmpty(): void
    {
        $actual = (new Socket())->jsonSerialize();

        self::assertSame([], $actual);
    }
}
