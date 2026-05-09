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

    /**
     * @param  array<string, string>  $parameters
     */
    protected function path(string $path, array $parameters = []): string
    {
        $versionedPath = '/v1'.$path;

        return preg_replace_callback('/\{([^}]+)\}/', function (array $matches) use ($parameters): string {
            $name = $matches[1];

            if (! array_key_exists($name, $parameters)) {
                throw new \InvalidArgumentException("Missing path parameter: $name");
            }

            return rawurlencode($parameters[$name]);
        }, $versionedPath);
    }

    /**
     * @param  array<array-key, mixed>  $query
     * @return array<array-key, mixed>
     */
    protected function getArray(string $path, array $query = []): array
    {
        return $this->expectArray($this->httpClient->get($path, $query));
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @param  array<string, string>  $headers
     * @return array<array-key, mixed>
     */
    protected function postArray(string $path, array $data = [], array $headers = []): array
    {
        return $this->expectArray($this->httpClient->post($path, $data, $headers));
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @param  array<string, string>  $headers
     * @return array<array-key, mixed>
     */
    protected function putArray(string $path, array $data = [], array $headers = []): array
    {
        return $this->expectArray($this->httpClient->put($path, $data, $headers));
    }

    /**
     * @param  array<array-key, mixed>  $query
     * @return array<array-key, mixed>
     */
    protected function deleteArray(string $path, array $query = []): array
    {
        return $this->expectArray($this->httpClient->delete($path, $query));
    }

    /**
     * @param  array<array-key, mixed>  $query
     */
    protected function getRaw(string $path, array $query = []): string
    {
        return $this->httpClient->getRaw($path, $query);
    }

    /**
     * @param  array<array-key, mixed>  $query
     */
    protected function getInt(string $path, array $query = []): int
    {
        $response = $this->httpClient->get($path, $query);

        if (! is_int($response)) {
            throw new \UnexpectedValueException('Expected API response to be an integer.');
        }

        return $response;
    }

    /**
     * @return array<array-key, mixed>
     */
    private function expectArray(mixed $response): array
    {
        if (! is_array($response)) {
            throw new \UnexpectedValueException('Expected API response to be an array.');
        }

        return $response;
    }
}
