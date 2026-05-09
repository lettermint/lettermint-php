<?php

use Lettermint\Client\HttpClient;
use Lettermint\Endpoints\ProjectsEndpoint;

beforeEach(function () {
    $this->httpClient = Mockery::mock(HttpClient::class);
    $this->endpoint = new ProjectsEndpoint($this->httpClient);
});

afterEach(function () {
    Mockery::close();
});

test('it lists projects', function () {
    $query = ['filter[search]' => 'production'];
    $this->httpClient->shouldReceive('get')->once()->with('/v1/projects', $query)->andReturn(['data' => []]);

    expect($this->endpoint->list($query))->toBe(['data' => []]);
});

test('it creates projects', function () {
    $data = ['name' => 'Production'];
    $this->httpClient->shouldReceive('post')->once()->with('/v1/projects', $data, [])->andReturn(['data' => ['id' => 'project-id']]);

    expect($this->endpoint->create($data))->toBe(['data' => ['id' => 'project-id']]);
});

test('it retrieves projects', function () {
    $query = ['include' => 'routes'];
    $this->httpClient->shouldReceive('get')->once()->with('/v1/projects/project-id', $query)->andReturn(['data' => ['id' => 'project-id']]);

    expect($this->endpoint->retrieve('project-id', $query))->toBe(['data' => ['id' => 'project-id']]);
});

test('it updates projects', function () {
    $data = ['name' => 'Production'];
    $this->httpClient->shouldReceive('put')->once()->with('/v1/projects/project-id', $data, [])->andReturn(['data' => ['id' => 'project-id']]);

    expect($this->endpoint->update('project-id', $data))->toBe(['data' => ['id' => 'project-id']]);
});

test('it deletes projects', function () {
    $this->httpClient->shouldReceive('delete')->once()->with('/v1/projects/project-id', [])->andReturn(['deleted' => true]);

    expect($this->endpoint->delete('project-id'))->toBe(['deleted' => true]);
});

test('it rotates project tokens', function () {
    $this->httpClient->shouldReceive('post')->once()->with('/v1/projects/project-id/rotate-token', [], [])->andReturn(['token' => 'new-token']);

    expect($this->endpoint->rotateToken('project-id'))->toBe(['token' => 'new-token']);
});

test('it updates project members', function () {
    $data = ['members' => ['member-id']];
    $this->httpClient->shouldReceive('put')->once()->with('/v1/projects/project-id/members', $data, [])->andReturn(['data' => []]);

    expect($this->endpoint->updateMembers('project-id', $data))->toBe(['data' => []]);
});

test('it adds project members', function () {
    $this->httpClient->shouldReceive('post')->once()->with('/v1/projects/project-id/members/member-id', [], [])->andReturn(['data' => []]);

    expect($this->endpoint->addMember('project-id', 'member-id'))->toBe(['data' => []]);
});

test('it removes project members', function () {
    $this->httpClient->shouldReceive('delete')->once()->with('/v1/projects/project-id/members/member-id', [])->andReturn(['data' => []]);

    expect($this->endpoint->removeMember('project-id', 'member-id'))->toBe(['data' => []]);
});

test('it lists project routes', function () {
    $query = ['filter[route_type]' => 'outbound'];
    $this->httpClient->shouldReceive('get')->once()->with('/v1/projects/project-id/routes', $query)->andReturn(['data' => []]);

    expect($this->endpoint->routes('project-id', $query))->toBe(['data' => []]);
});

test('it creates project routes', function () {
    $data = ['name' => 'Default'];
    $this->httpClient->shouldReceive('post')->once()->with('/v1/projects/project-id/routes', $data, [])->andReturn(['data' => ['id' => 'route-id']]);

    expect($this->endpoint->createRoute('project-id', $data))->toBe(['data' => ['id' => 'route-id']]);
});
