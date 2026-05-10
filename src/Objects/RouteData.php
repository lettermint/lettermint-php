<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $id
 * @property string $project_id
 * @property string $slug
 * @property string $name
 * @property 'transactional'|'broadcast'|'inbound' $route_type
 * @property bool $is_default
 * @property string $inbound_address
 * @property string $inbound_domain
 * @property string $inbound_domain_verified_at
 * @property float|int $inbound_spam_threshold
 * @property 'inline'|'url' $attachment_delivery
 * @property \Lettermint\Objects\ProjectListData $project
 * @property int $webhooks_count
 * @property int $suppressed_recipients_count
 * @property array<string, mixed>|list<\Lettermint\Objects\RouteStatisticData> $statistics
 * @property string $created_at
 * @property string $updated_at
 */
final class RouteData extends Resource
{
    protected static array $casts = [
        'project' => \Lettermint\Objects\ProjectData::class,
    ];
}
