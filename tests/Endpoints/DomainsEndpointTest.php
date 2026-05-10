<?php

use Lettermint\Client\HttpClient;
use Lettermint\Endpoints\DomainsEndpoint;

beforeEach(function () {
    $this->httpClient = Mockery::mock(HttpClient::class);
    $this->endpoint = new DomainsEndpoint($this->httpClient);
});

afterEach(function () {
    Mockery::close();
});

test('it lists domains', function () {
    $query = ['filter[status]' => 'verified'];
    $this->httpClient->shouldReceive('get')->once()->with('/v1/domains', $query)->andReturn(['data' => []]);

    expect($this->endpoint->list($query)->toArray())->toBe(['data' => []]);
});

test('it creates domains', function () {
    $data = ['domain' => 'example.com'];
    $this->httpClient->shouldReceive('post')->once()->with('/v1/domains', $data, [])->andReturn(['data' => ['id' => 'domain-id']]);

    expect($this->endpoint->create($data)->toArray())->toBe(['data' => ['id' => 'domain-id']]);
});

test('it retrieves domains', function () {
    $query = ['include' => 'dns_records'];
    $this->httpClient->shouldReceive('get')->once()->with('/v1/domains/domain-id', $query)->andReturn(['data' => ['id' => 'domain-id']]);

    expect($this->endpoint->retrieve('domain-id', $query)->toArray())->toBe(['data' => ['id' => 'domain-id']]);
});

test('it deletes domains', function () {
    $this->httpClient->shouldReceive('delete')->once()->with('/v1/domains/domain-id', [])->andReturn(['deleted' => true]);

    expect($this->endpoint->delete('domain-id')->toArray())->toBe(['deleted' => true]);
});

test('it verifies all dns records', function () {
    $this->httpClient->shouldReceive('post')->once()->with('/v1/domains/domain-id/dns-records/verify', [], [])->andReturn(['data' => []]);

    expect($this->endpoint->verifyDnsRecords('domain-id')->toArray())->toBe(['data' => []]);
});

test('it verifies a specific dns record', function () {
    $this->httpClient->shouldReceive('post')->once()->with('/v1/domains/domain-id/dns-records/record-id/verify', [], [])->andReturn(['data' => []]);

    expect($this->endpoint->verifyDnsRecord('domain-id', 'record-id')->toArray())->toBe(['data' => []]);
});

test('it updates domain projects', function () {
    $data = ['project_ids' => ['project-id']];
    $this->httpClient->shouldReceive('put')->once()->with('/v1/domains/domain-id/projects', $data, [])->andReturn(['data' => []]);

    expect($this->endpoint->updateProjects('domain-id', $data)->toArray())->toBe(['data' => []]);
});
