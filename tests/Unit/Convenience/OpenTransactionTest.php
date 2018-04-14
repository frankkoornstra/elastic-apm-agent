<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Convenience;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use TechDeCo\ElasticApmAgent\Convenience\ContextTags;
use TechDeCo\ElasticApmAgent\Convenience\OpenTransaction;
use TechDeCo\ElasticApmAgent\Message\Span;
use TechDeCo\ElasticApmAgent\Message\Timestamp;

final class OpenTransactionTest extends TestCase
{
    /**
     * @var UuidInterface
     */
    private $id;

    /**
     * @var Timestamp
     */
    private $timestamp;

    /**
     * @var UuidInterface
     */
    private $correlationId;

    /**
     * @var OpenTransaction
     */
    private $transaction;

    /**
     * @before
     */
    public function setUpDependencies(): void
    {
        $this->id            = Uuid::uuid4();
        $this->timestamp     = new Timestamp();
        $this->correlationId = Uuid::uuid4();
        $this->transaction   = new OpenTransaction(
            $this->id,
            'alloy',
            $this->timestamp,
            'nora',
            $this->correlationId
        );
    }

    public function testIdInTransaction(): void
    {
        self::assertSame(
            $this->id->toString(),
            $this->transaction->toTransaction()->jsonSerialize()['id']
        );
    }

    public function testNameInTransaction(): void
    {
        self::assertSame(
            'alloy',
            $this->transaction->toTransaction()->jsonSerialize()['name']
        );
    }

    public function testTimestampInTransaction(): void
    {
        self::assertSame(
            $this->timestamp->__toString(),
            $this->transaction->toTransaction()->jsonSerialize()['timestamp']
        );
    }

    public function testTypeInTransaction(): void
    {
        self::assertSame(
            'nora',
            $this->transaction->toTransaction()->jsonSerialize()['type']
        );
    }

    public function testCorrelationIdInTransaction(): void
    {
        self::assertSame(
            $this->correlationId->toString(),
            $this->transaction->toTransaction()->jsonSerialize()['context']['tags'][ContextTags::CORRELATION_ID]
        );
    }

    public function testSpanInTransaction(): void
    {
        $span = new Span(0, 'thunderjaw', 0, 'beast');
        $this->transaction->addSpan($span);

        self::assertEquals(
            $span->jsonSerialize(),
            $this->transaction->toTransaction()->jsonSerialize()['spans'][0]
        );
    }

    public function testMarkInTransaction(): void
    {
        $this->transaction->addMark('spear', 15.0);

        self::assertSame(
            15.0,
            $this->transaction->toTransaction()->jsonSerialize()['marks']['spear']
        );
    }

    public function testGetOffset(): void
    {
        self::assertGreaterThan(0, $this->transaction->getStartOffset());
    }

    public function testGetCorrelationId(): void
    {
        self::assertSame($this->correlationId, $this->transaction->getCorrelationId());
    }
}
