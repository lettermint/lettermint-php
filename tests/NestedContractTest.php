<?php

use Lettermint\Objects;

test('it hydrates named nested contract values and keeps array access', function (string $parent, string $field, string $child, bool $list) {
    $nested = ['fixture' => 'value'];
    $payload = [$field => $list ? [$nested] : $nested];
    $object = new $parent($payload);
    $value = $list ? $object->$field[0] : $object->$field;

    expect($value)->toBeInstanceOf($child)
        ->and($value['fixture'])->toBe('value')
        ->and($object->toArray())->toBe($payload)
        ->and(json_decode(json_encode($object, JSON_THROW_ON_ERROR), true))->toBe($payload)
        ->and((new $parent([$field => null]))->$field)->toBeNull()
        ->and((new $parent)->$field)->toBeNull();
    if ($list) {
        expect((new $parent([$field => []]))->$field)->toBe([]);
    }
})->with([
    [Objects\MessageData::class, 'to', Objects\MessageRecipientData::class, true],
    [Objects\MessageData::class, 'cc', Objects\MessageRecipientData::class, true],
    [Objects\MessageData::class, 'bcc', Objects\MessageRecipientData::class, true],
    [Objects\MessageData::class, 'attachments', Objects\MessageAttachmentData::class, true],
    [Objects\MessageListData::class, 'to', Objects\MessageRecipientData::class, true],
    [Objects\MessageListData::class, 'cc', Objects\MessageRecipientData::class, true],
    [Objects\MessageListData::class, 'bcc', Objects\MessageRecipientData::class, true],
    [Objects\ProjectData::class, 'routes', Objects\RouteData::class, true],
    [Objects\ProjectData::class, 'last_28_days', Objects\MessageStatsData::class, false],
    [Objects\RouteData::class, 'project', Objects\ProjectData::class, false],
    [Objects\StatsDailyData::class, 'transactional', Objects\StatsTypeData::class, false],
    [Objects\StatsDailyData::class, 'broadcast', Objects\StatsTypeData::class, false],
    [Objects\StatsTotalsData::class, 'transactional', Objects\StatsTypeData::class, false],
    [Objects\StatsTotalsData::class, 'broadcast', Objects\StatsTypeData::class, false],
    [Objects\StoreRouteData::class, 'settings', Objects\UpdateRouteSettingsData::class, false],
    [Objects\StoreRouteData::class, 'inbound_settings', Objects\UpdateRouteInboundSettingsData::class, false],
    [Objects\UpdateRouteData::class, 'settings', Objects\UpdateRouteSettingsData::class, false],
    [Objects\UpdateRouteData::class, 'inbound_settings', Objects\UpdateRouteInboundSettingsData::class, false],
]);

test('it keeps an optional nullable inbound route domain', function (?string $domain) {
    $route = new Objects\RouteData(['inbound_route_domain' => $domain]);
    expect($route->inbound_route_domain)->toBe($domain)
        ->and($route->toArray())->toBe(['inbound_route_domain' => $domain])
        ->and((new Objects\RouteData)->inbound_route_domain)->toBeNull();
})->with(['incoming.example.com', null]);
