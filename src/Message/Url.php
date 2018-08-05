<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Message;

use JsonSerializable;
use Psr\Http\Message\UriInterface;
use TechDeCo\ElasticApmAgent\Serialization;

final class Url implements JsonSerializable
{
    /**
     * @var string|null
     */
    private $raw;

    /**
     * @var string|null
     */
    private $protocol;

    /**
     * @var string|null
     */
    private $full;

    /**
     * @var string|null
     */
    private $hostname;

    /**
     * @var string|null
     */
    private $port;

    /**
     * @var string|null
     */
    private $path;

    /**
     * @var string|null
     */
    private $query;

    /**
     * @var string|null
     */
    private $fragment;

    private function __construct()
    {
    }

    public static function fromUri(UriInterface $uri): self
    {
        $me           = new self();
        $me->raw      = $uri->__toString();
        $me->protocol = $uri->getScheme();
        $me->full     = $uri->__toString();
        $me->hostname = $uri->getHost();
        $me->port     = $uri->getPort() ? (string) $uri->getPort() : null;
        $me->path     = $uri->getPath();
        $me->query    = $uri->getQuery() ?: null;
        $me->fragment = $uri->getFragment() ?: null;

        return $me;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        return Serialization::filterUnset([
            'raw' => $this->raw,
            'protocol' => $this->protocol,
            'full' => $this->full,
            'hostname' => $this->hostname,
            'port' => $this->port,
            'pathname' => $this->path,
            'search' => $this->query,
            'hash' => $this->fragment,
        ]);
    }
}
