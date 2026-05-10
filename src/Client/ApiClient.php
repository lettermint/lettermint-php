<?php

namespace Lettermint\Client;

use Lettermint\Client\Auth\TeamBearerTokenAuth;
use Lettermint\Endpoints\DomainsEndpoint;
use Lettermint\Endpoints\MessagesEndpoint;
use Lettermint\Endpoints\ProjectsEndpoint;
use Lettermint\Endpoints\RoutesEndpoint;
use Lettermint\Endpoints\StatsEndpoint;
use Lettermint\Endpoints\SuppressionsEndpoint;
use Lettermint\Endpoints\TeamEndpoint;
use Lettermint\Endpoints\WebhooksEndpoint;

/**
 * @property-read DomainsEndpoint $domains Access domain operations.
 * @property-read MessagesEndpoint $messages Access message operations.
 * @property-read ProjectsEndpoint $projects Access project operations.
 * @property-read RoutesEndpoint $routes Access route operations.
 * @property-read StatsEndpoint $stats Access statistics operations.
 * @property-read SuppressionsEndpoint $suppressions Access suppression operations.
 * @property-read TeamEndpoint $team Access team operations.
 * @property-read WebhooksEndpoint $webhooks Access webhook operations.
 */
class ApiClient
{
    private HttpClient $httpClient;

    private array $endpoints = [];

    protected array $endpointRegistry = [
        'domains' => DomainsEndpoint::class,
        'messages' => MessagesEndpoint::class,
        'projects' => ProjectsEndpoint::class,
        'routes' => RoutesEndpoint::class,
        'stats' => StatsEndpoint::class,
        'suppressions' => SuppressionsEndpoint::class,
        'team' => TeamEndpoint::class,
        'webhooks' => WebhooksEndpoint::class,
    ];

    public function __construct(string $apiToken, ?string $baseUrl = null)
    {
        $this->httpClient = new HttpClient(
            new TeamBearerTokenAuth($apiToken),
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

    public function ping(): string
    {
        return trim($this->httpClient->getRaw('/v1/ping'));
    }
}
