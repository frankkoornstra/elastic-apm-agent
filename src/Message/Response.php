<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Message;

use JsonSerializable;
use Psr\Http\Message\ResponseInterface;
use TechDeCo\ElasticApmAgent\Serialization;

final class Response implements JsonSerializable
{
    /**
     * @var bool|null
     */
    private $finished;

    /**
     * @var mixed[]
     */
    private $headerList = [];

    /**
     * @var bool|null
     */
    private $hasHeadersSent;

    /**
     * @var int|null
     */
    private $httpStatusCode;

    public static function fromHttpResponse(ResponseInterface $response): self
    {
        return (new self())->resultingInStatusCode($response->getStatusCode());
    }

    public function thatIsFinished(): self
    {
        $me           = clone $this;
        $me->finished = true;

        return $me;
    }

    public function thatIsNotFinished(): self
    {
        $me           = clone $this;
        $me->finished = false;

        return $me;
    }

    /**
     * @param mixed $value
     */
    public function withHeader(string $name, $value): self
    {
        $me                    = clone $this;
        $me->headerList[$name] = $value;

        return $me;
    }

    public function thatHasSentHeaders(): self
    {
        $me                 = clone $this;
        $me->hasHeadersSent = true;

        return $me;
    }

    public function thatHasNotSentHeaders(): self
    {
        $me                 = clone $this;
        $me->hasHeadersSent = false;

        return $me;
    }

    public function resultingInStatusCode(int $httpStatusCode): self
    {
        $me                 = clone $this;
        $me->httpStatusCode = $httpStatusCode;

        return $me;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        return Serialization::filterUnset([
            'finished' => $this->finished,
            'headers' => $this->headerList,
            'headers_sent' => $this->hasHeadersSent,
            'status_code' => $this->httpStatusCode,
        ]);
    }
}
