<?php

use Lettermint\Client\HttpClient;
use Lettermint\Client\TeamClient;
use Lettermint\Endpoints\DomainsEndpoint;
use Lettermint\Endpoints\MessagesEndpoint;
use Lettermint\Endpoints\ProjectsEndpoint;
use Lettermint\Endpoints\RoutesEndpoint;
use Lettermint\Endpoints\StatsEndpoint;
use Lettermint\Endpoints\SuppressionsEndpoint;
use Lettermint\Endpoints\TeamEndpoint;
use Lettermint\Endpoints\WebhooksEndpoint;

test('it exposes team endpoints', function () {
    $client = new TeamClient('team-token', 'http://api.example.com');

    expect($client->domains)->toBeInstanceOf(DomainsEndpoint::class);
    expect($client->messages)->toBeInstanceOf(MessagesEndpoint::class);
    expect($client->projects)->toBeInstanceOf(ProjectsEndpoint::class);
    expect($client->routes)->toBeInstanceOf(RoutesEndpoint::class);
    expect($client->stats)->toBeInstanceOf(StatsEndpoint::class);
    expect($client->suppressions)->toBeInstanceOf(SuppressionsEndpoint::class);
    expect($client->team)->toBeInstanceOf(TeamEndpoint::class);
    expect($client->webhooks)->toBeInstanceOf(WebhooksEndpoint::class);
});

test('it reuses endpoint instances', function () {
    $client = new TeamClient('team-token', 'http://api.example.com');

    expect($client->projects)->toBe($client->projects);
});

test('it owns an http client', function () {
    $client = new TeamClient('team-token', 'http://api.example.com');
    $reflection = new ReflectionClass($client);
    $property = $reflection->getProperty('httpClient');
    $property->setAccessible(true);

    expect($property->getValue($client))->toBeInstanceOf(HttpClient::class);
});

test('it pings the team API as a scalar status code', function () {
    $client = new TeamClient('team-token', 'http://api.example.com');
    $reflection = new ReflectionClass($client);
    $property = $reflection->getProperty('httpClient');
    $property->setAccessible(true);
    $httpClient = Mockery::mock(HttpClient::class);
    $httpClient->shouldReceive('get')->once()->with('/v1/ping')->andReturn(200);
    $property->setValue($client, $httpClient);

    expect($client->ping())->toBe(200);
});
