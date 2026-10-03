<?php

use Lettermint\Client\HttpClient;
use Lettermint\Endpoints\WebhooksEndpoint;
use Lettermint\Objects;

test('it keeps webhook credential states on create and update', function (array $state) {
    $http = Mockery::mock(HttpClient::class);
    $endpoint = new WebhooksEndpoint($http);
    $create = ['name' => 'Fixture', 'url' => 'https://example.test/hook', 'events' => ['message.sent'], ...$state];
    $http->shouldReceive('post')->once()->with('/v1/webhooks', $create, [])->andReturn(['data' => ['has_basic_auth' => true]]);
    $http->shouldReceive('put')->once()->with('/v1/webhooks/webhook-id', $state, [])->andReturn(['data' => ['has_basic_auth' => true]]);

    expect($endpoint->create($create)->data->has_basic_auth)->toBeTrue()
        ->and($endpoint->update('webhook-id', $state)->data->has_basic_auth)->toBeTrue();
})->with([
    'omit' => [[]],
    'set empty password' => [['basic_auth' => ['username' => ' fixture user ', 'password' => '']]],
    'remove' => [['basic_auth' => null]],
]);

test('it hydrates credentials without removing an explicit null', function (string $class) {
    $state = ['basic_auth' => ['username' => ' fixture user ', 'password' => '']];
    $request = new $class($state);

    expect($request->basic_auth)->toBeInstanceOf(Objects\WebhookBasicAuthData::class)
        ->and($request->basic_auth->password)->toBe('')
        ->and($request->toArray())->toBe($state)
        ->and((new $class(['basic_auth' => null]))->toArray())->toBe(['basic_auth' => null])
        ->and((new $class)->toArray())->toBe([]);
})->with([Objects\StoreWebhookData::class, Objects\UpdateWebhookData::class]);

test('it exposes the safe Basic Auth flag on all read models', function (string $class) {
    expect((new $class(['has_basic_auth' => true]))->has_basic_auth)->toBeTrue()
        ->and((new $class(['has_basic_auth' => false]))->has_basic_auth)->toBeFalse();
})->with([Objects\WebhookData::class, Objects\WebhookListData::class, Objects\WebhookSecretData::class]);
