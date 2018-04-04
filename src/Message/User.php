<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Message;

use JsonSerializable;
use TechDeCo\ElasticApmAgent\Serialization;

final class User implements JsonSerializable
{
    /**
     * @var string|int|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $email;

    /**
     * @var string
     */
    private $username;

    /**
     * @param string|int $id
     */
    public function withId($id): self
    {
        $me     = clone $this;
        $me->id = $id;

        return $me;
    }

    public function withEmail(string $email): self
    {
        $me        = clone $this;
        $me->email = $email;

        return $me;
    }

    public function withUsername(string $username): self
    {
        $me           = clone $this;
        $me->username = $username;

        return $me;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        return Serialization::filterUnset([
            'id' => $this->id,
            'email' => $this->email,
            'username' => $this->username,
        ]);
    }
}
