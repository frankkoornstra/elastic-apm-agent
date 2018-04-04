<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Message;

use PHPUnit\Framework\TestCase;
use TechDeCo\ElasticApmAgent\Message\Response;

final class ResponseTest extends TestCase
{
    public function testAll(): void
    {
        $actual   = (new Response())
            ->thatIsFinished()
            ->withHeader('content-type', 'application/json')
            ->thatHasSentHeaders()
            ->resultingInStatusCode(204)
            ->jsonSerialize();
        $expected = [
            'finished' => true,
            'headers' => ['content-type' => 'application/json'],
            'headers_sent' => true,
            'status_code' => 204,
        ];

        self::assertEquals($expected, $actual);
    }

    public function testNotFinished(): void
    {
        $actual = (new Response())->thatIsNotFinished()->jsonSerialize();

        self::assertFalse($actual['finished']);
    }

    public function testHasNotSentHeaders(): void
    {
        $actual = (new Response())->thatHasNotSentHeaders()->jsonSerialize();

        self::assertFalse($actual['headers_sent']);
    }

    public function testFiltersEmpty(): void
    {
        $actual = (new Response())->jsonSerialize();

        self::assertSame([], $actual);
    }
}
