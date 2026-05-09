<?php

namespace Lettermint\Client\Auth;

class SendingApiTokenAuth implements AuthStrategy
{
    public function __construct(private readonly string $apiToken) {}

    public function headers(): array
    {
        return ['x-lettermint-token' => $this->apiToken];
    }

    public function token(): string
    {
        return $this->apiToken;
    }
}
