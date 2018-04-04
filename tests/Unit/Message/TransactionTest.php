<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Message;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use TechDeCo\ElasticApmAgent\Message\Context;
use TechDeCo\ElasticApmAgent\Message\Span;
use TechDeCo\ElasticApmAgent\Message\Transaction;

final class TransactionTest extends TestCase
{
    public function testAll(): void
    {
        $id      = Uuid::uuid4();
        $date    = new DateTimeImmutable('2018-02-14T10:11:12.131+01:00');
        $context = (new Context())->withTag('beast', 'thunderjaw');
        $span    = new Span(1.2, 'mysql', 0.0, 'database');

        $actual = (new Transaction(13.2, $id, 'alloy', $date, 'zeta'))
            ->inContext($context)
            ->resultingIn('204')
            ->withSpan($span)
            ->marking('loaded', 5.3)
            ->thatIsSampled()
            ->withTotalDroppedSpans(5)
            ->jsonSerialize();

        $expected = [
            'context' => [
                'tags' => [
                    'beast' => 'thunderjaw',
                ],
            ],
            'duration' => 13.2,
            'id' => (string) $id,
            'name' => 'alloy',
            'result' => '204',
            'timestamp' => '2018-02-14T10:11:12.131+01:00',
            'spans' => [
                [
                    'duration' => 1.2,
                    'name' => 'mysql',
                    'start' => 0.0,
                    'type' => 'database',
                ],
            ],
            'type' => 'zeta',
            'marks' => [
                'loaded' => 5.3,
            ],
            'sampled' => true,
            'span_count' => [
                'dropped' => [
                    'total' => 5,
                ],
            ],
        ];

        self::assertEquals($expected, $actual);
    }

    public function testNotSampled(): void
    {
        $id   = Uuid::uuid4();
        $date = new DateTimeImmutable('2018-02-14T10:11:12.131+01:00');

        $actual = (new Transaction(13.2, $id, 'alloy', $date, 'zeta'))
            ->thatIsNotSampled()
            ->jsonSerialize();

        self::assertFalse($actual['sampled']);
    }

    public function testFiltersEmpty(): void
    {
        $id   = Uuid::uuid4();
        $date = new DateTimeImmutable('2018-02-14T10:11:12.131+01:00');

        $actual = (new Transaction(13.2, $id, 'alloy', $date, 'zeta'))
            ->jsonSerialize();

        $expected = [
            'duration' => 13.2,
            'id' => (string) $id,
            'name' => 'alloy',
            'timestamp' => '2018-02-14T10:11:12.131+01:00',
            'type' => 'zeta',
        ];

        self::assertEquals($expected, $actual);
    }
}
