<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Lettermint\Client\Auth\SendingApiTokenAuth;
use Lettermint\Client\Auth\TeamBearerTokenAuth;
use Lettermint\Client\HttpClient;

function makeHttpClientWithHistory(object $auth, array &$container): HttpClient
{
    $mock = new MockHandler([
        new Response(200, [], json_encode(['ok' => true])),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $handlerStack->push(Middleware::history($container));

    $guzzle = new Client(['handler' => $handlerStack]);

    $client = new HttpClient($auth, 'http://api.example.com');
    $reflection = new ReflectionClass($client);
    $property = $reflection->getProperty('client');
    $property->setAccessible(true);
    $property->setValue($client, $guzzle);

    return $client;
}

test('sending auth uses only x-lettermint-token', function () {
    $container = [];
    $client = makeHttpClientWithHistory(new SendingApiTokenAuth('sending-token'), $container);

    $client->get('/ping');

    expect($container[0]['request']->getHeaderLine('x-lettermint-token'))->toBe('sending-token');
    expect($container[0]['request']->hasHeader('Authorization'))->toBeFalse();
});

test('team auth uses only bearer authorization', function () {
    $container = [];
    $client = makeHttpClientWithHistory(new TeamBearerTokenAuth('team-token'), $container);

    $client->get('/ping');

    expect($container[0]['request']->getHeaderLine('Authorization'))->toBe('Bearer team-token');
    expect($container[0]['request']->hasHeader('x-lettermint-token'))->toBeFalse();
});
