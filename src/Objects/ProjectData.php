<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $id
 * @property string $name
 * @property bool $smtp_enabled
 * @property bool $redact_email_content
 * @property string|null $default_route_id
 * @property string|null $token_generated_at
 * @property string|null $token_last_used_at
 * @property string|null $token_last_used_ip
 * @property list<RouteData> $routes
 * @property int $routes_count
 * @property list<DomainData> $domains
 * @property int $domains_count
 * @property MessageStatsData|null $last_28_days
 * @property string $created_at
 * @property string $updated_at
 * @property 'live'|'sandbox' $delivery_mode
 */
final class ProjectData extends Resource
{
    protected static array $casts = [
        'last_28_days' => MessageStatsData::class,
        'routes' => [RouteData::class],
        'domains' => [DomainData::class],
    ];
}
