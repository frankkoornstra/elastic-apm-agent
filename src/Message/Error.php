<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Message;

use JsonSerializable;
use Ramsey\Uuid\UuidInterface;
use TechDeCo\ElasticApmAgent\Serialization;

final class Error implements JsonSerializable
{
    /**
     * @var Context|null
     */
    private $context;

    /**
     * @var string|null
     */
    private $culprit;

    /**
     * @var Exception|null
     */
    private $exception;

    /**
     * @var UuidInterface|null
     */
    private $id;

    /**
     * @var Log|null
     */
    private $log;

    /**
     * @var Timestamp
     */
    private $timestamp;

    /**
     * @var UuidInterface|null
     */
    private $transactionId;

    private function __construct()
    {
    }

    public static function fromException(Exception $exception, Timestamp $timestamp): self
    {
        $me            = new Error();
        $me->exception = $exception;
        $me->timestamp = $timestamp;

        return $me;
    }

    public static function fromLog(Log $log, Timestamp $timestamp): self
    {
        $me            = new Error();
        $me->log       = $log;
        $me->timestamp = $timestamp;

        return $me;
    }

    public function inContext(Context $context): self
    {
        $me          = clone $this;
        $me->context = $context;

        return $me;
    }

    public function withCulprit(string $culprit): self
    {
        $me          = clone $this;
        $me->culprit = $culprit;

        return $me;
    }

    public function causedByException(Exception $exception): self
    {
        $me            = clone $this;
        $me->exception = $exception;

        return $me;
    }

    public function withId(UuidInterface $id): self
    {
        $me     = clone $this;
        $me->id = $id;

        return $me;
    }

    public function withLog(Log $log): self
    {
        $me      = clone $this;
        $me->log = $log;

        return $me;
    }

    public function correlatedToTransactionId(UuidInterface $id): self
    {
        $me                = clone $this;
        $me->transactionId = $id;

        return $me;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        return Serialization::filterUnset([
            'context' => Serialization::serializeOr($this->context),
            'culprit' => $this->culprit,
            'exception' => Serialization::serializeOr($this->exception),
            'id' => $this->id ? $this->id->toString() : null,
            'log' => Serialization::serializeOr($this->log),
            'timestamp' => Serialization::serializeOr($this->timestamp),
            'transaction' => Serialization::serializeOr($this->transactionId),
        ]);
    }
}
