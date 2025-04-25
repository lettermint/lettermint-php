<?php

use Lettermint\Lettermint;
use Lettermint\Client\HttpClient;
use Lettermint\Endpoints\EmailEndpoint;

beforeEach(function () {
    $this->apiToken = 'test-token';
    $this->baseUrl = 'http://api.lettermint.co/v1';
    $this->client = new Lettermint($this->apiToken, $this->baseUrl);
});

test('it can be instantiated with api token and base url', function () {
    expect($this->client)->toBeInstanceOf(Lettermint::class);
});

test('it creates an http client instance', function () {
    $reflection = new ReflectionClass($this->client);
    $property = $reflection->getProperty('httpClient');
    $property->setAccessible(true);

    expect($property->getValue($this->client))->toBeInstanceOf(HttpClient::class);
});

test('it creates an email endpoint instance', function () {
    expect($this->client->email)->toBeInstanceOf(EmailEndpoint::class);
});

test('it uses provided base url', function () {
    $customUrl = 'https://localhost/v1';
    $client = new Lettermint($this->apiToken, $customUrl);

    $reflection = new ReflectionClass($client);
    $property = $reflection->getProperty('baseUrl');
    $property->setAccessible(true);

    expect($property->getValue($client))->toBe($customUrl);
});

test('it uses default base url when none provided', function () {
    $client = new Lettermint($this->apiToken);

    $reflection = new ReflectionClass($client);
    $property = $reflection->getProperty('baseUrl');
    $property->setAccessible(true);

    expect($property->getValue($client))->toBe('https://api.lettermint.co/v1');
});
