<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent;

use function rtrim;

final class ClientConfiguration
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $token;

    public function __construct(string $url)
    {
        $this->url = rtrim($url, '/');
    }

    public function getTransactionsEndpoint(): string
    {
        return $this->url . '/v1/transactions';
    }

    public function getErrorsEndpoint(): string
    {
        return $this->url . '/v1/errors';
    }

    public function authenticatedByToken(string $token): self
    {
        $config        = clone $this;
        $config->token = $token;

        return $config;
    }

    public function needsAuthentication(): bool
    {
        return $this->token !== null;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
