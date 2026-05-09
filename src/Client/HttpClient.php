<?php

namespace Lettermint\Client;

use Composer\InstalledVersions;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Lettermint\Client\Auth\AuthStrategy;
use Lettermint\Client\Auth\SendingApiTokenAuth;

/**
 * @phpstan-type RequestHeaders array<string, string>
 * @phpstan-type RequestBody array<array-key, mixed>
 * @phpstan-type ApiResponse array<array-key, mixed>|int|string|bool|null
 */
class HttpClient
{
    private AuthStrategy $auth;

    private string $baseUrl;

    private Client $client;

    public function __construct(AuthStrategy|string $auth, string $baseUrl)
    {
        $this->auth = is_string($auth) ? new SendingApiTokenAuth($auth) : $auth;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 15,
            'headers' => [
                'Content-Type' => 'application/json',
                ...$this->auth->headers(),
                'User-Agent' => sprintf(
                    'Lettermint/%s (PHP; PHP %s)',
                    InstalledVersions::getPrettyVersion('lettermint/lettermint-php') ?? 'unknown',
                    PHP_VERSION
                ),
            ],
        ]);
    }

    /**
     * Fluent-style usage with dedicated endpoints (builder pattern)
     *
     * @param  RequestBody  $data  Request payload
     * @param  RequestHeaders  $headers  Additional headers for this request
     * @return ApiResponse Resulting API response
     *
     * @throws \Exception On HTTP or decode failure
     */
    public function post(string $path, array $data, array $headers = []): mixed
    {
        return $this->request('post', $path, ['json' => $data], $headers);
    }

    /**
     * @param  RequestBody  $query  Query parameters
     * @param  RequestHeaders  $headers  Additional headers for this request
     * @return ApiResponse Resulting API response
     *
     * @throws \Exception On HTTP or decode failure
     */
    public function get(string $path, array $query = [], array $headers = []): mixed
    {
        return $this->request('get', $path, ['query' => $query], $headers);
    }

    /**
     * @param  RequestBody  $data  Request payload
     * @param  RequestHeaders  $headers  Additional headers for this request
     * @return ApiResponse Resulting API response
     *
     * @throws \Exception On HTTP or decode failure
     */
    public function put(string $path, array $data, array $headers = []): mixed
    {
        return $this->request('put', $path, ['json' => $data], $headers);
    }

    /**
     * @param  RequestBody  $query  Query parameters
     * @param  RequestHeaders  $headers  Additional headers for this request
     * @return ApiResponse Resulting API response
     *
     * @throws \Exception On HTTP or decode failure
     */
    public function delete(string $path, array $query = [], array $headers = []): mixed
    {
        return $this->request('delete', $path, ['query' => $query], $headers);
    }

    /**
     * @param  RequestBody  $query  Query parameters
     * @param  RequestHeaders  $headers  Additional headers for this request
     *
     * @throws \Exception On HTTP failure
     */
    public function getRaw(string $path, array $query = [], array $headers = []): string
    {
        try {
            $requestOptions = [];

            if ($query !== []) {
                $requestOptions['query'] = $query;
            }

            $requestOptions['headers'] = [
                ...$this->withoutAuthHeaders($headers),
                ...$this->auth->headers(),
            ];

            return $this->client->get($path, $requestOptions)->getBody()->getContents();
        } catch (GuzzleException $e) {
            throw new \Exception('API request failed: '.$this->redactToken($e->getMessage()), 0, $e);
        }
    }

    /**
     * @param  array<string, mixed>  $options
     * @param  RequestHeaders  $headers
     *
     * @phpstan-return ApiResponse
     *
     * @throws \Exception
     */
    private function request(string $method, string $path, array $options = [], array $headers = []): mixed
    {
        try {
            $requestOptions = array_filter(
                $options,
                fn (mixed $value): bool => ! is_array($value) || $value !== []
            );

            $requestOptions['headers'] = [
                ...$this->withoutAuthHeaders($headers),
                ...$this->auth->headers(),
            ];

            $response = $this->client->{$method}($path, $requestOptions);
            $body = $response->getBody()->getContents();
            $result = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Could not decode API response');
            }

            return $result;
        } catch (GuzzleException $e) {
            throw new \Exception('API request failed: '.$this->redactToken($e->getMessage()), 0, $e);
        }
    }

    private function redactToken(string $message): string
    {
        $token = $this->auth->token();

        return str_replace([$token, 'Bearer '.$token], '[redacted]', $message);
    }

    /**
     * @param  RequestHeaders  $headers
     * @return RequestHeaders
     */
    private function withoutAuthHeaders(array $headers): array
    {
        return array_filter(
            $headers,
            fn (string $name): bool => ! in_array(strtolower($name), ['authorization', 'x-lettermint-token'], true),
            ARRAY_FILTER_USE_KEY
        );
    }
}
