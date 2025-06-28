<?php

namespace Lettermint\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class HttpClient
{
    private string $apiToken;

    private string $baseUrl;

    private Client $client;

    public function __construct(string $apiToken, string $baseUrl)
    {
        $this->apiToken = $apiToken;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 15,
            'headers' => [
                'Content-Type' => 'application/json',
                'x-lettermint-token' => $this->apiToken,
            ],
        ]);
    }

    /**
     * Fluent-style usage with dedicated endpoints (builder pattern)
     *
     * @param  array  $headers  Additional headers for this request
     * @return array Resulting API response
     *
     * @throws \Exception On HTTP or decode failure
     */
    public function post(string $path, array $data, array $headers = []): array
    {
        try {
            $requestOptions = ['json' => $data];

            if (! empty($headers)) {
                $requestOptions['headers'] = $headers;
            }

            $response = $this->client->post($path, $requestOptions);
            $body = $response->getBody()->getContents();
            $result = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Could not decode API response');
            }

            return $result;
        } catch (GuzzleException $e) {
            throw new \Exception('API request failed: '.$e->getMessage(), 0, $e);
        }
    }
}
