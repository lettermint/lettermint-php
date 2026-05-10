<?php

use Lettermint\Client\HttpClient;
use Lettermint\Endpoints\DomainsEndpoint;
use Lettermint\Endpoints\EmailEndpoint;
use Lettermint\Responses\DomainListResponse;
use Lettermint\Responses\SendBatchMailResponse;
use Lettermint\Responses\SendMailResponse;

test('sending endpoints return typed response resources', function () {
    $httpClient = Mockery::mock(HttpClient::class);
    $endpoint = new EmailEndpoint($httpClient);

    $httpClient
        ->shouldReceive('post')
        ->once()
        ->with('/v1/send', ['from' => 'sender@example.com', 'to' => ['user@example.com'], 'subject' => 'Hi'], [])
        ->andReturn(['message_id' => 'message-id', 'status' => 'queued']);

    $response = $endpoint->send([
        'from' => 'sender@example.com',
        'to' => ['user@example.com'],
        'subject' => 'Hi',
    ]);

    expect($response)->toBeInstanceOf(SendMailResponse::class)
        ->and($response->message_id)->toBe('message-id')
        ->and($response->status)->toBe('queued')
        ->and($response->toArray())->toBe(['message_id' => 'message-id', 'status' => 'queued']);
});

test('batch sending wraps list responses in typed resources', function () {
    $httpClient = Mockery::mock(HttpClient::class);
    $endpoint = new EmailEndpoint($httpClient);

    $httpClient
        ->shouldReceive('post')
        ->once()
        ->with('/v1/send/batch', [['from' => 'sender@example.com', 'to' => ['user@example.com'], 'subject' => 'Hi']], [])
        ->andReturn([['message_id' => 'message-id', 'status' => 'queued']]);

    $response = $endpoint->sendBatch([
        ['from' => 'sender@example.com', 'to' => ['user@example.com'], 'subject' => 'Hi'],
    ]);

    expect($response)->toBeInstanceOf(SendBatchMailResponse::class)
        ->and($response->data[0])->toBeInstanceOf(SendMailResponse::class)
        ->and($response->data[0]->message_id)->toBe('message-id');
});

test('paginated endpoints hydrate typed data resources', function () {
    $httpClient = Mockery::mock(HttpClient::class);
    $endpoint = new DomainsEndpoint($httpClient);

    $httpClient
        ->shouldReceive('get')
        ->once()
        ->with('/v1/domains', [])
        ->andReturn([
            'data' => [
                [
                    'id' => 'domain-id',
                    'domain' => 'example.com',
                    'status' => 'verified',
                    'status_changed_at' => null,
                    'created_at' => '2026-05-10T00:00:00Z',
                ],
            ],
            'path' => null,
            'per_page' => 10,
            'next_cursor' => null,
            'next_page_url' => null,
            'prev_cursor' => null,
            'prev_page_url' => null,
        ]);

    $response = $endpoint->list();

    expect($response)->toBeInstanceOf(DomainListResponse::class)
        ->and($response->data[0]->domain)->toBe('example.com')
        ->and($response->data[0]->status)->toBe('verified');
});
