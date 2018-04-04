<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Message;

use JsonSerializable;
use TechDeCo\ElasticApmAgent\Serialization;

final class Request implements JsonSerializable
{
    /**
     * @var mixed[]|string|null
     */
    private $body;

    /**
     * @var mixed[]
     */
    private $environment = [];

    /**
     * @var mixed[]
     */
    private $headerList = [];

    /**
     * @var string|null
     */
    private $httpVersion;

    /**
     * @var string
     */
    private $method;

    /**
     * @var Socket|null
     */
    private $socket;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var string[]
     */
    private $cookieList = [];

    public function __construct(string $method, Url $url)
    {
        $this->method = $method;
        $this->url    = $url;
    }

    /**
     * @param mixed[]|string $body
     */
    public function withBody($body): self
    {
        $me       = clone $this;
        $me->body = $body;

        return $me;
    }

    /**
     * @param mixed $value
     */
    public function withEnvironmentVariable(string $name, $value): self
    {
        $me                     = clone $this;
        $me->environment[$name] = $value;

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

    public function onHttpVersion(string $httpVersion): self
    {
        $me              = clone $this;
        $me->httpVersion = $httpVersion;

        return $me;
    }

    public function onSocket(Socket $socket): self
    {
        $me         = clone $this;
        $me->socket = $socket;

        return $me;
    }

    public function withCookie(string $key, string $value): self
    {
        $me                   = clone $this;
        $me->cookieList[$key] = $value;

        return $me;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        return Serialization::filterUnset([
            'body' => $this->body,
            'env' => $this->environment,
            'headers' => $this->headerList,
            'http_version' => $this->httpVersion,
            'method' => $this->method,
            'socket' => $this->socket ? $this->socket->jsonSerialize() : null,
            'url' => $this->url->jsonSerialize(),
            'cookies' => $this->cookieList,
        ]);
    }
}
