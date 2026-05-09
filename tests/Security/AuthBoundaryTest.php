<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Lettermint\Client\SendingClient;
use Lettermint\Client\TeamClient;

function replaceWrappedGuzzleClient(object $sdkClient, array &$container): void
{
    $mock = new MockHandler([
        new Response(200, [], '200'),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $handlerStack->push(Middleware::history($container));

    $guzzle = new Client(['handler' => $handlerStack]);

    $sdkReflection = new ReflectionClass($sdkClient);
    $httpClientProperty = $sdkReflection->getProperty('httpClient');
    $httpClientProperty->setAccessible(true);
    $httpClient = $httpClientProperty->getValue($sdkClient);

    $httpReflection = new ReflectionClass($httpClient);
    $guzzleProperty = $httpReflection->getProperty('client');
    $guzzleProperty->setAccessible(true);
    $guzzleProperty->setValue($httpClient, $guzzle);
}

test('sending client sends only sending auth', function () {
    $container = [];
    $client = new SendingClient('sending-secret', 'http://api.example.com');
    replaceWrappedGuzzleClient($client, $container);

    $client->ping();

    expect($container[0]['request']->getHeaderLine('x-lettermint-token'))->toBe('sending-secret');
    expect($container[0]['request']->hasHeader('Authorization'))->toBeFalse();
});

test('team client sends only bearer auth', function () {
    $container = [];
    $client = new TeamClient('team-secret', 'http://api.example.com');
    replaceWrappedGuzzleClient($client, $container);

    $client->ping();

    expect($container[0]['request']->getHeaderLine('Authorization'))->toBe('Bearer team-secret');
    expect($container[0]['request']->hasHeader('x-lettermint-token'))->toBeFalse();
});
