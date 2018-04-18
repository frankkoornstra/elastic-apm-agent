<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Convenience;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use TechDeCo\ElasticApmAgent\Message\Context;
use TechDeCo\ElasticApmAgent\Message\Span;
use TechDeCo\ElasticApmAgent\Message\Timestamp;
use TechDeCo\ElasticApmAgent\Message\Transaction;
use function microtime;

final class OpenTransaction
{
    /**
     * @var float
     */
    private $startOfTransaction;

    /**
     * @var UuidInterface
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Timestamp
     */
    private $timestamp;

    /**
     * @var Span[]
     */
    private $spanList = [];

    /**
     * @var string
     */
    private $type;

    /**
     * @var mixed[]
     */
    private $markList = [];

    /**
     * @var UuidInterface
     */
    private $correlationId;

    public function __construct(
        UuidInterface $id,
        string $name,
        Timestamp $timestamp,
        string $type,
        ?UuidInterface $correlationId = null
    ) {
        $this->id                 = $id;
        $this->name               = $name;
        $this->timestamp          = $timestamp;
        $this->type               = $type;
        $this->startOfTransaction = microtime(true);
        $this->correlationId      = $correlationId ?? Uuid::uuid4();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function addSpan(Span $span): void
    {
        $this->spanList[] = $span;
    }

    public function addMark(string $group, string $event, float $timestamp): void
    {
        if (! isset($this->markList[$group])) {
            $this->markList[$group] = [];
        }

        $this->markList[$group][$event] = $timestamp;
    }

    public function toTransaction(): Transaction
    {
        $transaction = new Transaction(
            (microtime(true) - $this->startOfTransaction) * 1000,
            $this->id,
            $this->name,
            $this->timestamp,
            $this->type
        );
        $transaction = $transaction->withSpan(...$this->spanList);

        foreach ($this->markList as $group => $eventList) {
            foreach ($eventList as $event => $timestamp) {
                $transaction = $transaction->marking($group, $event, $timestamp);
            }
        }

        $transaction = $transaction->inContext(
            (new Context())->withTag(ContextTags::CORRELATION_ID, $this->correlationId->toString())
        );

        return $transaction;
    }

    /**
     * @return float The offset in microseconds with microsecond precision
     */
    public function getStartOffset(): float
    {
        return (microtime(true) - $this->startOfTransaction) * 1000;
    }

    public function getCorrelationId(): UuidInterface
    {
        return $this->correlationId;
    }
}
