<?php

namespace Lettermint;

use Lettermint\Client\ApiClient;
use Lettermint\Client\Auth\SendingApiTokenAuth;
use Lettermint\Client\HttpClient;
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

    public static function api(string $apiToken, ?string $baseUrl = null): ApiClient
    {
        return new ApiClient($apiToken, $baseUrl);
    }

    public static function email(string $apiToken, ?string $baseUrl = null): EmailEndpoint
    {
        return new EmailEndpoint(new HttpClient(
            new SendingApiTokenAuth($apiToken),
            $baseUrl ?? 'https://api.lettermint.co/v1'
        ));
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
