<?php

use Lettermint\Client\HttpClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Middleware;

beforeEach(function () {
    $this->apiToken = 'test-token';
    $this->baseUrl = 'http://api.example.com';
});

test('it properly constructs with api token and base url', function () {
    $client = new HttpClient($this->apiToken, $this->baseUrl);

    expect($client)->toBeInstanceOf(HttpClient::class);
});

test('it properly handles successful API responses', function () {
    $mockResponse = [
        'message_id' => '123abc',
        'status' => 'pending'
    ];

    $mock = new MockHandler([
        new Response(202, [], json_encode($mockResponse))
    ]);

    $handlerStack = HandlerStack::create($mock);
    $container = [];
    $history = Middleware::history($container);
    $handlerStack->push($history);

    $mockGuzzle = new Client([
        'handler' => $handlerStack,
        'headers' => [
            'Content-Type' => 'application/json',
            'x-lettermint-token' => $this->apiToken,
        ]
    ]);

    $client = new HttpClient($this->apiToken, $this->baseUrl);
    $reflection = new ReflectionClass($client);
    $property = $reflection->getProperty('client');
    $property->setAccessible(true);
    $property->setValue($client, $mockGuzzle);

    $result = $client->post('/send', [
        'from' => 'test@example.com',
        'to' => ['recipient@example.com'],
        'subject' => 'Test Email'
    ]);

    expect($result)->toBe($mockResponse);

    expect($container[0]['request']->getHeaders())->toHaveKey('x-lettermint-token');
    expect($container[0]['request']->getHeader('x-lettermint-token')[0])->toBe($this->apiToken);
    expect($container[0]['request']->getHeader('Content-Type')[0])->toBe('application/json');
});

test('it throws exception on invalid JSON response', function () {
    $mock = new MockHandler([
        new Response(200, [], 'invalid-json')
    ]);

    $handlerStack = HandlerStack::create($mock);
    $mockGuzzle = new Client(['handler' => $handlerStack]);

    $client = new HttpClient($this->apiToken, $this->baseUrl);
    $reflection = new ReflectionClass($client);
    $property = $reflection->getProperty('client');
    $property->setAccessible(true);
    $property->setValue($client, $mockGuzzle);

    expect(fn() => $client->post('/send', [
        'from' => 'test@example.com',
        'to' => ['recipient@example.com'],
        'subject' => 'Test Email'
    ]))->toThrow(\Exception::class, 'Could not decode API response');
});

test('it throws exception on API error', function () {
    $mock = new MockHandler([
        new Response(400, [], json_encode(['error' => 'Bad Request']))
    ]);

    $handlerStack = HandlerStack::create($mock);
    $mockGuzzle = new Client(['handler' => $handlerStack]);

    $client = new HttpClient($this->apiToken, $this->baseUrl);
    $reflection = new ReflectionClass($client);
    $property = $reflection->getProperty('client');
    $property->setAccessible(true);
    $property->setValue($client, $mockGuzzle);

    expect(fn() => $client->post('/send', [
        'from' => 'test@example.com',
        'to' => ['recipient@example.com'],
        'subject' => 'Test Email'
    ]))->toThrow(\Exception::class, 'API request failed');
});

test('it trims trailing slashes from base url', function () {
    $baseUrlWithSlash = 'http://api.example.com/';
    $client = new HttpClient($this->apiToken, $baseUrlWithSlash);

    $reflection = new ReflectionClass($client);
    $property = $reflection->getProperty('baseUrl');
    $property->setAccessible(true);

    expect($property->getValue($client))->toBe('http://api.example.com');
});
