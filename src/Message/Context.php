<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Message;

use JsonSerializable;
use TechDeCo\ElasticApmAgent\Serialization;

final class Context implements JsonSerializable
{
    /**
     * @var mixed[]|null
     */
    private $custom;

    /**
     * @var Response|null
     */
    private $response;

    /**
     * @var Request|null
     */
    private $request;

    /**
     * @var string[]
     */
    private $tagList = [];

    /**
     * @var User|null
     */
    private $user;

    /**
     * @param mixed $value
     */
    public function withCustomVariable(string $name, $value): self
    {
        $me                = clone $this;
        $me->custom[$name] = $value;

        return $me;
    }

    public function withResponse(Response $response): self
    {
        $me           = clone $this;
        $me->response = $response;

        return $me;
    }

    public function withRequest(Request $request): self
    {
        $me          = clone $this;
        $me->request = $request;

        return $me;
    }

    public function withTag(string $tag, string $value): self
    {
        $me                = clone $this;
        $me->tagList[$tag] = $value;

        return $me;
    }

    public function withUser(User $user): self
    {
        $me       = clone $this;
        $me->user = $user;

        return $me;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        return Serialization::filterUnset([
            'custom' => $this->custom,
            'response' => $this->response ? $this->response->jsonSerialize() : null,
            'request' => $this->request ? $this->request->jsonSerialize() : null,
            'tags' => $this->tagList,
            'user' => $this->user,
        ]);
    }
}
