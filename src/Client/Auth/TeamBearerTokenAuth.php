<?php

namespace Lettermint\Client\Auth;

class TeamBearerTokenAuth implements AuthStrategy
{
    public function __construct(private readonly string $apiToken) {}

    public function headers(): array
    {
        return ['Authorization' => 'Bearer '.$this->apiToken];
    }

    public function token(): string
    {
        return $this->apiToken;
    }
}
