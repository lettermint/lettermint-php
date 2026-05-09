<?php

use Lettermint\Client\HttpClient;
use Lettermint\Client\SendingClient;
use Lettermint\Endpoints\EmailEndpoint;

test('it exposes the email endpoint', function () {
    $client = new SendingClient('sending-token', 'http://api.example.com');

    expect($client->email)->toBeInstanceOf(EmailEndpoint::class);
});

test('it reuses endpoint instances', function () {
    $client = new SendingClient('sending-token', 'http://api.example.com');

    expect($client->email)->toBe($client->email);
});

test('it owns an http client', function () {
    $client = new SendingClient('sending-token', 'http://api.example.com');
    $reflection = new ReflectionClass($client);
    $property = $reflection->getProperty('httpClient');
    $property->setAccessible(true);

    expect($property->getValue($client))->toBeInstanceOf(HttpClient::class);
});

test('it pings the sending API as a scalar status code', function () {
    $client = new SendingClient('sending-token', 'http://api.example.com');
    $reflection = new ReflectionClass($client);
    $property = $reflection->getProperty('httpClient');
    $property->setAccessible(true);
    $httpClient = Mockery::mock(HttpClient::class);
    $httpClient->shouldReceive('get')->once()->with('/v1/ping')->andReturn(200);
    $property->setValue($client, $httpClient);

    expect($client->ping())->toBe(200);
});
