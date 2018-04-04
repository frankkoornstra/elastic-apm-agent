<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Message;

use JsonSerializable;
use TechDeCo\ElasticApmAgent\Serialization;
use function array_merge;

final class Process implements JsonSerializable
{
    /**
     * @var int
     */
    private $processId;

    /**
     * @var int|null
     */
    private $parentProcessId;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string[]
     */
    private $argumentList = [];

    public function __construct(int $processId)
    {
        $this->processId = $processId;
    }

    public function withParentProcessId(int $id): self
    {
        $me                  = clone $this;
        $me->parentProcessId = $id;

        return $me;
    }

    public function titled(string $title): self
    {
        $me        = clone $this;
        $me->title = $title;

        return $me;
    }

    public function withArguments(string ...$argument): self
    {
        $me               = clone $this;
        $me->argumentList = array_merge($me->argumentList, $argument);

        return $me;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        return Serialization::filterUnset([
            'pid' => $this->processId,
            'ppid' => $this->parentProcessId,
            'title' => $this->title,
            'argv' => $this->argumentList,
        ]);
    }
}
