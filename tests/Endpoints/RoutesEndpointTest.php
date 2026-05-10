<?php

use Lettermint\Client\HttpClient;
use Lettermint\Endpoints\RoutesEndpoint;

beforeEach(function () {
    $this->httpClient = Mockery::mock(HttpClient::class);
    $this->endpoint = new RoutesEndpoint($this->httpClient);
});

afterEach(function () {
    Mockery::close();
});

test('it retrieves routes', function () {
    $query = ['include' => 'domain'];
    $this->httpClient->shouldReceive('get')->once()->with('/v1/routes/route-id', $query)->andReturn(['data' => ['id' => 'route-id']]);

    expect($this->endpoint->retrieve('route-id', $query)->toArray())->toBe(['data' => ['id' => 'route-id']]);
});

test('it updates routes', function () {
    $data = ['name' => 'Default'];
    $this->httpClient->shouldReceive('put')->once()->with('/v1/routes/route-id', $data, [])->andReturn(['data' => ['id' => 'route-id']]);

    expect($this->endpoint->update('route-id', $data)->toArray())->toBe(['data' => ['id' => 'route-id']]);
});

test('it deletes routes', function () {
    $this->httpClient->shouldReceive('delete')->once()->with('/v1/routes/route-id', [])->andReturn(['deleted' => true]);

    expect($this->endpoint->delete('route-id')->toArray())->toBe(['deleted' => true]);
});

test('it verifies inbound domains', function () {
    $this->httpClient->shouldReceive('post')->once()->with('/v1/routes/route-id/verify-inbound-domain', [], [])->andReturn(['data' => []]);

    expect($this->endpoint->verifyInboundDomain('route-id')->toArray())->toBe(['data' => []]);
});
