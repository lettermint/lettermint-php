<?php

namespace Lettermint;

use Lettermint\Client\HttpClient;
use Lettermint\Client\SendingClient;
use Lettermint\Client\TeamClient;
use Lettermint\Endpoints\EmailEndpoint;

/**
 * @property-read EmailEndpoint $email Access the send endpoint {@see EmailEndpoint}
 */
class Lettermint
{
    private string $apiToken;

    private string $baseUrl;

    private HttpClient $httpClient;

    private array $endpoints = [];

    protected array $endpointRegistry = [
        'email' => EmailEndpoint::class,
    ];

    public function __construct(string $apiToken, ?string $baseUrl = null)
    {
        $this->apiToken = $apiToken;
        $this->baseUrl = $baseUrl ?? 'https://api.lettermint.co/v1';
        $this->httpClient = new HttpClient($this->apiToken, $this->baseUrl);
    }

    public static function sending(string $apiToken, ?string $baseUrl = null): SendingClient
    {
        return new SendingClient($apiToken, $baseUrl);
    }

    public static function team(string $apiToken, ?string $baseUrl = null): TeamClient
    {
        return new TeamClient($apiToken, $baseUrl);
    }

    public function __get($name)
    {
        if (isset($this->endpoints[$name])) {
            return $this->endpoints[$name];
        }

        if (array_key_exists($name, $this->endpointRegistry)) {
            $class = $this->endpointRegistry[$name];
            $this->endpoints[$name] = new $class($this->httpClient);

            return $this->endpoints[$name];
        }

        throw new \InvalidArgumentException("Unknown endpoint: $name");
    }
}
