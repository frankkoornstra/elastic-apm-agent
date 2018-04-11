<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Middleware;

use Ramsey\Uuid\UuidInterface;
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

    public function __construct(
        UuidInterface $id,
        string $name,
        Timestamp $timestamp,
        string $type
    ) {
        $this->id                 = $id;
        $this->name               = $name;
        $this->timestamp          = $timestamp;
        $this->type               = $type;
        $this->startOfTransaction = microtime(true);
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function addSpan(Span $span): void
    {
        $this->spanList[] = $span;
    }

    public function addMark(string $event, float $timestamp): void
    {
        $this->markList[$event] = $timestamp;
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

        foreach ($this->markList as $event => $timestamp) {
            $transaction = $transaction->marking($event, $timestamp);
        }

        return $transaction;
    }
}
