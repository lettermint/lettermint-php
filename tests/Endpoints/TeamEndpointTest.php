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

    expect($this->endpoint->retrieve($query)->toArray())->toBe(['data' => ['id' => 'team-id']]);
});

test('it updates the team', function () {
    $data = ['name' => 'Lettermint'];
    $this->httpClient->shouldReceive('put')->once()->with('/v1/team', $data, [])->andReturn(['data' => ['id' => 'team-id']]);

    expect($this->endpoint->update($data)->toArray())->toBe(['data' => ['id' => 'team-id']]);
});

test('it retrieves team usage', function () {
    $this->httpClient->shouldReceive('get')->once()->with('/v1/team/usage', [])->andReturn(['data' => []]);

    expect($this->endpoint->usage()->toArray())->toBe(['data' => []]);
});

test('it retrieves reusable team roles', function () {
    $this->httpClient->shouldReceive('get')->once()->with('/v1/team/roles', [])->andReturn(['data' => []]);

    expect($this->endpoint->roles()->toArray())->toBe(['data' => []]);
});

test('it retrieves team members', function () {
    $query = ['page[size]' => 10];
    $this->httpClient->shouldReceive('get')->once()->with('/v1/team/members', $query)->andReturn(['data' => []]);

    expect($this->endpoint->members($query)->toArray())->toBe(['data' => []]);
});

test('it retrieves a team member', function () {
    $this->httpClient->shouldReceive('get')->once()->with('/v1/team/members/user%2Fid', [])->andReturn(['id' => 'user/id']);

    expect($this->endpoint->member('user/id')->toArray())->toBe(['id' => 'user/id']);
});

test('it updates a team member assignment', function () {
    $data = [
        'role_id' => 'role-id',
        'project_access' => ['scope' => 'all'],
    ];
    $this->httpClient->shouldReceive('put')->once()->with('/v1/team/members/user%2Fid/assignment', $data, [])->andReturn(['id' => 'user/id']);

    expect($this->endpoint->updateMemberAssignment('user/id', $data)->toArray())->toBe(['id' => 'user/id']);
});
