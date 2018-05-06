<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Message;

use JsonSerializable;
use TechDeCo\ElasticApmAgent\Serialization;

final class Service implements JsonSerializable
{
    /**
     * @var VersionedName
     */
    private $agent;

    /**
     * @var string
     */
    private $name;

    /**
     * @var VersionedName|null
     */
    private $framework;

    /**
     * @var VersionedName|null
     */
    private $language;

    /**
     * @var string|null
     */
    private $environment;

    /**
     * @var VersionedName|null
     */
    private $runtime;

    /**
     * @var string|null
     */
    private $version;

    public function __construct(VersionedName $agent, string $name)
    {
        $this->agent = $agent;
        $this->name  = $name;
    }

    public function usingFramework(VersionedName $framework): self
    {
        $me            = clone $this;
        $me->framework = $framework;

        return $me;
    }

    public function usingLanguage(VersionedName $language): self
    {
        $me           = clone $this;
        $me->language = $language;

        return $me;
    }

    public function inEnvironment(string $environment): self
    {
        $me              = clone $this;
        $me->environment = $environment;

        return $me;
    }

    public function withRuntime(VersionedName $runtime): self
    {
        $me          = clone $this;
        $me->runtime = $runtime;

        return $me;
    }

    public function atVersion(string $version): self
    {
        $me          = clone $this;
        $me->version = $version;

        return $me;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        return Serialization::filterUnset([
            'agent' => Serialization::serializeOr($this->agent),
            'framework' => Serialization::serializeOr($this->framework),
            'language' => Serialization::serializeOr($this->language),
            'name' => $this->name,
            'environment' => $this->environment,
            'runtime' => Serialization::serializeOr($this->runtime),
            'version' => $this->version,
        ]);
    }
}
