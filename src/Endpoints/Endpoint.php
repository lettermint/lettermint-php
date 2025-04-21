<?php

namespace Lettermint\Endpoints;

use Lettermint\Client\HttpClient;

abstract class Endpoint
{
    protected HttpClient $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }
}
