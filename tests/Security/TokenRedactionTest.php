<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use Lettermint\Client\Auth\SendingApiTokenAuth;
use Lettermint\Client\Auth\TeamBearerTokenAuth;
use Lettermint\Client\HttpClient;

function httpClientFailingWith(string $message, object $auth): HttpClient
{
    $request = new Request('GET', 'http://api.example.com/v1/ping');
    $mock = new MockHandler([
        new RequestException($message, $request),
    ]);

    $guzzle = new Client(['handler' => HandlerStack::create($mock)]);

    $client = new HttpClient($auth, 'http://api.example.com');
    $reflection = new ReflectionClass($client);
    $property = $reflection->getProperty('client');
    $property->setAccessible(true);
    $property->setValue($client, $guzzle);

    return $client;
}

test('it redacts sending tokens from exception messages', function () {
    $client = httpClientFailingWith('Request failed with sending-secret', new SendingApiTokenAuth('sending-secret'));

    try {
        $client->get('/v1/ping');
    } catch (Exception $exception) {
        expect($exception->getMessage())->not->toContain('sending-secret');
        expect($exception->getMessage())->toContain('[redacted]');

        return;
    }

    $this->fail('Expected exception was not thrown.');
});

test('it redacts team bearer tokens from exception messages', function () {
    $client = httpClientFailingWith('Request failed with Bearer team-secret', new TeamBearerTokenAuth('team-secret'));

    try {
        $client->get('/v1/ping');
    } catch (Exception $exception) {
        expect($exception->getMessage())->not->toContain('team-secret');
        expect($exception->getMessage())->toContain('[redacted]');

        return;
    }

    $this->fail('Expected exception was not thrown.');
});
