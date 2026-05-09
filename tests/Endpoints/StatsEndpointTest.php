<?php

use Lettermint\Client\HttpClient;
use Lettermint\Endpoints\StatsEndpoint;

beforeEach(function () {
    $this->httpClient = Mockery::mock(HttpClient::class);
    $this->endpoint = new StatsEndpoint($this->httpClient);
});

afterEach(function () {
    Mockery::close();
});

test('it retrieves stats', function () {
    $query = ['from' => '2026-05-01', 'to' => '2026-05-09'];
    $this->httpClient->shouldReceive('get')->once()->with('/v1/stats', $query)->andReturn(['data' => []]);

    expect($this->endpoint->retrieve($query))->toBe(['data' => []]);
});
