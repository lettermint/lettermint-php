<?php

namespace Lettermint\Client\Auth;

interface AuthStrategy
{
    /**
     * @return array<string, string>
     */
    public function headers(): array;

    public function token(): string;
}
