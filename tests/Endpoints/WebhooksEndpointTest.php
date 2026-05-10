<?php

use Lettermint\Client\HttpClient;
use Lettermint\Endpoints\WebhooksEndpoint;

beforeEach(function () {
    $this->httpClient = Mockery::mock(HttpClient::class);
    $this->endpoint = new WebhooksEndpoint($this->httpClient);
});

afterEach(function () {
    Mockery::close();
});

test('it lists webhooks', function () {
    $query = ['filter[enabled]' => true];
    $this->httpClient->shouldReceive('get')->once()->with('/v1/webhooks', $query)->andReturn(['data' => []]);

    expect($this->endpoint->list($query)->toArray())->toBe(['data' => []]);
});

test('it creates webhooks', function () {
    $data = ['route_id' => 'route-id', 'name' => 'Webhook', 'url' => 'https://example.com', 'events' => ['message.sent']];
    $this->httpClient->shouldReceive('post')->once()->with('/v1/webhooks', $data, [])->andReturn(['data' => ['id' => 'webhook-id']]);

    expect($this->endpoint->create($data)->toArray())->toBe(['data' => ['id' => 'webhook-id']]);
});

test('it retrieves webhooks', function () {
    $this->httpClient->shouldReceive('get')->once()->with('/v1/webhooks/webhook-id', [])->andReturn(['data' => ['id' => 'webhook-id']]);

    expect($this->endpoint->retrieve('webhook-id')->toArray())->toBe(['data' => ['id' => 'webhook-id']]);
});

test('it updates webhooks', function () {
    $data = ['name' => 'Webhook'];
    $this->httpClient->shouldReceive('put')->once()->with('/v1/webhooks/webhook-id', $data, [])->andReturn(['data' => ['id' => 'webhook-id']]);

    expect($this->endpoint->update('webhook-id', $data)->toArray())->toBe(['data' => ['id' => 'webhook-id']]);
});

test('it deletes webhooks', function () {
    $this->httpClient->shouldReceive('delete')->once()->with('/v1/webhooks/webhook-id', [])->andReturn(['deleted' => true]);

    expect($this->endpoint->delete('webhook-id')->toArray())->toBe(['deleted' => true]);
});

test('it tests webhooks', function () {
    $this->httpClient->shouldReceive('post')->once()->with('/v1/webhooks/webhook-id/test', [], [])->andReturn(['queued' => true]);

    expect($this->endpoint->test('webhook-id')->toArray())->toBe(['queued' => true]);
});

test('it regenerates webhook secrets', function () {
    $this->httpClient->shouldReceive('post')->once()->with('/v1/webhooks/webhook-id/regenerate-secret', [], [])->andReturn(['secret' => 'secret']);

    expect($this->endpoint->regenerateSecret('webhook-id')->toArray())->toBe(['secret' => 'secret']);
});

test('it lists webhook deliveries', function () {
    $query = ['filter[status]' => 'failed'];
    $this->httpClient->shouldReceive('get')->once()->with('/v1/webhooks/webhook-id/deliveries', $query)->andReturn(['data' => []]);

    expect($this->endpoint->deliveries('webhook-id', $query)->toArray())->toBe(['data' => []]);
});

test('it retrieves webhook deliveries', function () {
    $this->httpClient->shouldReceive('get')->once()->with('/v1/webhooks/webhook-id/deliveries/delivery-id', [])->andReturn(['data' => ['id' => 'delivery-id']]);

    expect($this->endpoint->delivery('webhook-id', 'delivery-id')->toArray())->toBe(['data' => ['id' => 'delivery-id']]);
});
