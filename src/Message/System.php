<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Message;

use JsonSerializable;
use TechDeCo\ElasticApmAgent\Serialization;

final class System implements JsonSerializable
{
    /**
     * @var string|null
     */
    private $architecture;

    /**
     * @var string|null
     */
    private $hostname;

    /**
     * @var string|null
     */
    private $platform;

    public function onArchitecture(string $architecture): self
    {
        $me               = clone $this;
        $me->architecture = $architecture;

        return $me;
    }

    public function atHost(string $hostname): self
    {
        $me           = clone $this;
        $me->hostname = $hostname;

        return $me;
    }

    public function onPlatform(string $platform): self
    {
        $me           = clone $this;
        $me->platform = $platform;

        return $me;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        return Serialization::filterUnset([
            'hostname' => $this->hostname,
            'architecture' => $this->architecture,
            'platform' => $this->platform,
        ]);
    }
}
