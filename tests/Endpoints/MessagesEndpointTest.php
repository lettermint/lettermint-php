<?php

use Lettermint\Client\HttpClient;
use Lettermint\Endpoints\MessagesEndpoint;

beforeEach(function () {
    $this->httpClient = Mockery::mock(HttpClient::class);
    $this->endpoint = new MessagesEndpoint($this->httpClient);
});

afterEach(function () {
    Mockery::close();
});

test('it lists messages', function () {
    $query = ['filter[status]' => 'delivered'];
    $this->httpClient->shouldReceive('get')->once()->with('/v1/messages', $query)->andReturn(['data' => []]);

    expect($this->endpoint->list($query))->toBe(['data' => []]);
});

test('it retrieves messages', function () {
    $this->httpClient->shouldReceive('get')->once()->with('/v1/messages/message-id', [])->andReturn(['data' => ['id' => 'message-id']]);

    expect($this->endpoint->retrieve('message-id'))->toBe(['data' => ['id' => 'message-id']]);
});

test('it retrieves message events', function () {
    $query = ['include_machine_events' => true];
    $this->httpClient->shouldReceive('get')->once()->with('/v1/messages/message-id/events', $query)->andReturn(['data' => []]);

    expect($this->endpoint->events('message-id', $query))->toBe(['data' => []]);
});

test('it retrieves message source', function () {
    $this->httpClient->shouldReceive('getRaw')->once()->with('/v1/messages/message-id/source', [])->andReturn('raw');

    expect($this->endpoint->source('message-id'))->toBe('raw');
});

test('it retrieves message html', function () {
    $this->httpClient->shouldReceive('getRaw')->once()->with('/v1/messages/message-id/html', [])->andReturn('<p>Hello</p>');

    expect($this->endpoint->html('message-id'))->toBe('<p>Hello</p>');
});

test('it retrieves message text', function () {
    $this->httpClient->shouldReceive('getRaw')->once()->with('/v1/messages/message-id/text', [])->andReturn('Hello');

    expect($this->endpoint->text('message-id'))->toBe('Hello');
});
