<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Message;

use JsonSerializable;
use TechDeCo\ElasticApmAgent\Serialization;
use function array_merge;

final class Span implements JsonSerializable
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var mixed[]
     */
    private $context = [];

    /**
     * @var float
     */
    private $duration;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int|null
     */
    private $parentId;

    /**
     * @var StackTraceFrame[]
     */
    private $stackTraceFrameList = [];

    /**
     * @var float
     */
    private $startOffset;

    /**
     * @var string
     */
    private $type;

    public function __construct(float $duration, string $name, float $startOffset, string $type)
    {
        $this->duration    = $duration;
        $this->name        = $name;
        $this->startOffset = $startOffset;
        $this->type        = $type;
    }

    public function withId(int $id): self
    {
        $me     = clone $this;
        $me->id = $id;

        return $me;
    }

    /**
     * @param mixed $value
     */
    public function withContext(string $name, $value): self
    {
        $me                 = clone $this;
        $me->context[$name] = $value;

        return $me;
    }

    public function withParent(Span $parent): self
    {
        $me           = clone $this;
        $me->parentId = $parent->id;

        return $me;
    }

    public function withStackTraceFrame(StackTraceFrame ...$frame): self
    {
        $me                      = clone $this;
        $me->stackTraceFrameList = array_merge($me->stackTraceFrameList, $frame);

        return $me;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        return Serialization::filterUnset([
            'id' => $this->id,
            'context' => $this->context,
            'duration' => $this->duration,
            'name' => $this->name,
            'parent' => $this->parentId,
            'stacktrace' => Serialization::serialize(...$this->stackTraceFrameList),
            'start' => $this->startOffset,
            'type' => $this->type,
        ]);
    }
}
