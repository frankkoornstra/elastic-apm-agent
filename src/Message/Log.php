<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Message;

use JsonSerializable;
use TechDeCo\ElasticApmAgent\Serialization;
use function array_merge;

final class Log implements JsonSerializable
{
    /**
     * @var string|null
     */
    private $level;

    /**
     * @var string|null
     */
    private $loggerName;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string|null
     */
    private $parameterizedMessage;

    /**
     * @var StackTraceFrame[]
     */
    private $stackTraceFrameList = [];

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function withSeverityLevel(string $level): self
    {
        $me        = clone $this;
        $me->level = $level;

        return $me;
    }

    public function byLoggerNamed(string $name): self
    {
        $me             = clone $this;
        $me->loggerName = $name;

        return $me;
    }

    public function withParameterizedMessage(string $message): self
    {
        $me                       = clone $this;
        $me->parameterizedMessage = $message;

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
            'level' => $this->level,
            'logger_name' => $this->loggerName,
            'message' => $this->message,
            'param_message' => $this->parameterizedMessage,
            'stacktrace' => Serialization::serialize(...$this->stackTraceFrameList),
        ]);
    }
}
