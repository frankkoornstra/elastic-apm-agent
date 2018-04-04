<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Message;

use JsonSerializable;
use TechDeCo\ElasticApmAgent\Serialization;
use function array_merge;

final class Exception implements JsonSerializable
{
    /**
     * @var string|int|null
     */
    private $code;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $module;

    /**
     * @var mixed[]
     */
    private $attributeList = [];

    /**
     * @var StackTraceFrame[]
     */
    private $stackTraceFrameList = [];

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var bool|null
     */
    private $isHandled;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * @param string|int $code
     */
    public function withCode($code): self
    {
        $me       = clone $this;
        $me->code = $code;

        return $me;
    }

    public function inModule(string $module): self
    {
        $me         = clone $this;
        $me->module = $module;

        return $me;
    }

    /**
     * @param mixed $value
     */
    public function withAttribute(string $name, $value): self
    {
        $me                       = clone $this;
        $me->attributeList[$name] = $value;

        return $me;
    }

    public function withStackTraceFrame(StackTraceFrame ...$frame): self
    {
        $me                      = clone $this;
        $me->stackTraceFrameList = array_merge($me->stackTraceFrameList, $frame);

        return $me;
    }

    public function asType(string $type): self
    {
        $me       = clone $this;
        $me->type = $type;

        return $me;
    }

    public function thatIsHandled(): self
    {
        $me            = clone $this;
        $me->isHandled = true;

        return $me;
    }

    public function thatIsNotHandled(): self
    {
        $me            = clone $this;
        $me->isHandled = false;

        return $me;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        return Serialization::filterUnset([
            'code' => $this->code,
            'message' => $this->message,
            'module' => $this->module,
            'attributes' => $this->attributeList,
            'stacktrace' => Serialization::serialize(...$this->stackTraceFrameList),
            'type' => $this->type,
            'handled' => $this->isHandled,
        ]);
    }
}
