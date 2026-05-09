<?php

namespace Lettermint\Client;

use Lettermint\Client\Auth\SendingApiTokenAuth;
use Lettermint\Endpoints\EmailEndpoint;

/**
 * @property-read EmailEndpoint $email Access Sending API email operations.
 */
class SendingClient
{
    private HttpClient $httpClient;

    private array $endpoints = [];

    protected array $endpointRegistry = [
        'email' => EmailEndpoint::class,
    ];

    public function __construct(string $apiToken, ?string $baseUrl = null)
    {
        $this->httpClient = new HttpClient(
            new SendingApiTokenAuth($apiToken),
            $baseUrl ?? 'https://api.lettermint.co/v1'
        );
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

    public function ping(): int
    {
        $response = $this->httpClient->get('/v1/ping');

        if (! is_int($response)) {
            throw new \UnexpectedValueException('Expected API response to be an integer.');
        }

        return $response;
    }
}
