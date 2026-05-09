<?php

use Lettermint\Client\HttpClient;
use Lettermint\Endpoints\TeamEndpoint;

beforeEach(function () {
    $this->httpClient = Mockery::mock(HttpClient::class);
    $this->endpoint = new TeamEndpoint($this->httpClient);
});

afterEach(function () {
    Mockery::close();
});

test('it retrieves the team', function () {
    $query = ['include' => 'billing'];
    $this->httpClient->shouldReceive('get')->once()->with('/v1/team', $query)->andReturn(['data' => ['id' => 'team-id']]);

    expect($this->endpoint->retrieve($query))->toBe(['data' => ['id' => 'team-id']]);
});

test('it updates the team', function () {
    $data = ['name' => 'Lettermint'];
    $this->httpClient->shouldReceive('put')->once()->with('/v1/team', $data, [])->andReturn(['data' => ['id' => 'team-id']]);

    expect($this->endpoint->update($data))->toBe(['data' => ['id' => 'team-id']]);
});

test('it retrieves team usage', function () {
    $this->httpClient->shouldReceive('get')->once()->with('/v1/team/usage', [])->andReturn(['data' => []]);

    expect($this->endpoint->usage())->toBe(['data' => []]);
});

test('it retrieves team members', function () {
    $query = ['page[size]' => 10];
    $this->httpClient->shouldReceive('get')->once()->with('/v1/team/members', $query)->andReturn(['data' => []]);

    expect($this->endpoint->members($query))->toBe(['data' => []]);
});
