<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Message;

use JsonSerializable;
use TechDeCo\ElasticApmAgent\Serialization;
use function array_merge;

final class StackTraceFrame implements JsonSerializable
{
    /**
     * @var string|null
     */
    private $absolutePath;

    /**
     * @var int|null
     */
    private $columnNumber;

    /**
     * @var string|null
     */
    private $contextLine;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string|null
     */
    private $function;

    /**
     * @var bool|null
     */
    private $isLibraryFrame;

    /**
     * @var int
     */
    private $lineNumber;

    /**
     * @var string|null
     */
    private $module;

    /**
     * @var string[]
     */
    private $postContext = [];

    /**
     * @var string[]
     */
    private $preContext = [];

    /**
     * @var mixed[]
     */
    private $localVariableList = [];

    public function __construct(string $fileName, int $lineNumber)
    {
        $this->fileName   = $fileName;
        $this->lineNumber = $lineNumber;
    }

    public function atPath(string $absolutePath): self
    {
        $me               = clone $this;
        $me->absolutePath = $absolutePath;

        return $me;
    }

    public function atColumnNumber(int $columnNumber): self
    {
        $me               = clone $this;
        $me->columnNumber = $columnNumber;

        return $me;
    }

    public function withLineContext(string $line): self
    {
        $me              = clone $this;
        $me->contextLine = $line;

        return $me;
    }

    public function inFunction(string $function): self
    {
        $me           = clone $this;
        $me->function = $function;

        return $me;
    }

    public function originatingFromLibraryCode(): self
    {
        $me                 = clone $this;
        $me->isLibraryFrame = true;

        return $me;
    }

    public function originatingFromUserCode(): self
    {
        $me                 = clone $this;
        $me->isLibraryFrame = false;

        return $me;
    }

    public function inModule(string $module): self
    {
        $me         = clone $this;
        $me->module = $module;

        return $me;
    }

    public function withPostContext(string ...$line): self
    {
        $me              = clone $this;
        $me->postContext = array_merge($me->postContext, $line);

        return $me;
    }

    public function withPreContext(string ...$line): self
    {
        $me             = clone $this;
        $me->preContext = array_merge($me->preContext, $line);

        return $me;
    }

    /**
     * @param mixed $value
     */
    public function withLocalVariable(string $name, $value): self
    {
        $me                           = clone $this;
        $me->localVariableList[$name] = $value;

        return $me;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        return Serialization::filterUnset([
            'abs_path' => $this->absolutePath,
            'colno' => $this->columnNumber,
            'context_line' => $this->contextLine,
            'filename' => $this->fileName,
            'function' => $this->function,
            'library_frame' => $this->isLibraryFrame,
            'lineno' => $this->lineNumber,
            'module' => $this->module,
            'post_context' => $this->postContext,
            'pre_context' => $this->preContext,
            'vars' => $this->localVariableList,
        ]);
    }
}
