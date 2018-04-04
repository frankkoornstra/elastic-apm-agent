<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Message;

use JsonSerializable;
use TechDeCo\ElasticApmAgent\Serialization;

final class Socket implements JsonSerializable
{
    /**
     * @var bool|null
     */
    private $isEncrypteo;

    /**
     * @var string|null
     */
    private $remoteAddress;

    public function thatIsEncrypted(): self
    {
        $me              = clone $this;
        $me->isEncrypteo = true;

        return $me;
    }

    public function thatIsNotEncrypted(): self
    {
        $me              = clone $this;
        $me->isEncrypteo = false;

        return $me;
    }

    public function fromRemoteAddress(string $remoteAddress): self
    {
        $me                = clone $this;
        $me->remoteAddress = $remoteAddress;

        return $me;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        return Serialization::filterUnset([
            'remote_address' => $this->remoteAddress,
            'encrypted' => $this->isEncrypteo,
        ]);
    }
}
