<?php

use Lettermint\Client\HttpClient;
use Lettermint\Endpoints\Endpoint;

// Create a concrete implementation of the abstract Endpoint class for testing
class TestEndpoint extends Endpoint
{
    // This class is empty as we just need it to test the abstract class functionality
}

beforeEach(function () {
    $this->httpClient = Mockery::mock(HttpClient::class);
    $this->endpoint = new TestEndpoint($this->httpClient);
});

afterEach(function () {
    Mockery::close();
});

test('it can be instantiated with http client', function () {
    expect($this->endpoint)->toBeInstanceOf(Endpoint::class);
});

test('it stores http client instance', function () {
    $reflection = new ReflectionClass($this->endpoint);
    $property = $reflection->getProperty('httpClient');
    $property->setAccessible(true);

    expect($property->getValue($this->endpoint))->toBe($this->httpClient);
});

test('it allows access to protected http client in child classes', function () {
    $testEndpoint = new class($this->httpClient) extends Endpoint {
        public function getHttpClient(): HttpClient
        {
            return $this->httpClient;
        }
    };

    expect($testEndpoint->getHttpClient())->toBe($this->httpClient);
});
