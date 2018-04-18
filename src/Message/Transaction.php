<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Message;

use JsonSerializable;
use Ramsey\Uuid\UuidInterface;
use TechDeCo\ElasticApmAgent\Serialization;
use function array_merge;

final class Transaction implements JsonSerializable
{
    /**
     * @var Context|null
     */
    private $context;

    /**
     * @var float
     */
    private $duration;

    /**
     * @var UuidInterface
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $result;

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
     * @var bool|null
     */
    private $isSampled;

    /**
     * @var int
     */
    private $droppedTotalSpanCount;

    public function __construct(
        float $duration,
        UuidInterface $id,
        string $name,
        Timestamp $timestamp,
        string $type
    ) {
        $this->duration  = $duration;
        $this->id        = $id;
        $this->name      = $name;
        $this->timestamp = $timestamp;
        $this->type      = $type;
    }

    public function inContext(Context $context): self
    {
        $me          = clone $this;
        $me->context = $context;

        return $me;
    }

    public function resultingIn(string $result): self
    {
        $me         = clone $this;
        $me->result = $result;

        return $me;
    }

    public function withSpan(Span ...$span): self
    {
        $me           = clone $this;
        $me->spanList = array_merge($me->spanList, $span);

        return $me;
    }

    public function marking(string $group, string $event, float $timestamp): self
    {
        $me = clone $this;

        if (! isset($me->markList[$group])) {
            $me->markList[$group] = [];
        }
        $me->markList[$group][$event] = $timestamp;

        return $me;
    }

    public function thatIsSampled(): self
    {
        $me            = clone $this;
        $me->isSampled = true;

        return $me;
    }

    public function thatIsNotSampled(): self
    {
        $me            = clone $this;
        $me->isSampled = false;

        return $me;
    }

    public function withTotalDroppedSpans(int $count): self
    {
        $me                        = clone $this;
        $me->droppedTotalSpanCount = $count;

        return $me;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        return Serialization::filterUnset([
            'context' => $this->context ? $this->context->jsonSerialize() : null,
            'duration' => $this->duration,
            'id' => $this->id->toString(),
            'name' => $this->name,
            'result' => $this->result,
            'timestamp' => $this->timestamp->jsonSerialize(),
            'spans' => Serialization::serialize(...$this->spanList),
            'type' => $this->type,
            'marks' => $this->markList,
            'sampled' => $this->isSampled,
            'span_count' => $this->droppedTotalSpanCount ? ['dropped' => ['total' => $this->droppedTotalSpanCount]] : null,
        ]);
    }
}
