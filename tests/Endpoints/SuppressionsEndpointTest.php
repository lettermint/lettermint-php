<?php

use Lettermint\Client\HttpClient;
use Lettermint\Endpoints\SuppressionsEndpoint;

beforeEach(function () {
    $this->httpClient = Mockery::mock(HttpClient::class);
    $this->endpoint = new SuppressionsEndpoint($this->httpClient);
});

afterEach(function () {
    Mockery::close();
});

test('it lists suppressions', function () {
    $query = ['filter[value]' => 'user@example.com'];
    $this->httpClient->shouldReceive('get')->once()->with('/v1/suppressions', $query)->andReturn(['data' => []]);

    expect($this->endpoint->list($query)->toArray())->toBe(['data' => []]);
});

test('it creates suppressions', function () {
    $data = ['email' => 'user@example.com', 'reason' => 'manual', 'scope' => 'team'];
    $this->httpClient->shouldReceive('post')->once()->with('/v1/suppressions', $data, [])->andReturn(['data' => ['id' => 'suppression-id']]);

    expect($this->endpoint->create($data)->toArray())->toBe(['data' => ['id' => 'suppression-id']]);
});

test('it deletes suppressions', function () {
    $this->httpClient->shouldReceive('delete')->once()->with('/v1/suppressions/suppression-id', [])->andReturn(['deleted' => true]);

    expect($this->endpoint->delete('suppression-id')->toArray())->toBe(['deleted' => true]);
});
