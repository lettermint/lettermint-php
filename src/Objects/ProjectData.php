<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $id
 * @property string $name
 * @property bool $smtp_enabled
 * @property string|null $default_route_id
 * @property string|null $token_generated_at
 * @property string|null $token_last_used_at
 * @property string|null $token_last_used_ip
 * @property list<\Lettermint\Objects\RouteListData> $routes
 * @property int $routes_count
 * @property list<\Lettermint\Objects\DomainData> $domains
 * @property int $domains_count
 * @property list<\Lettermint\Objects\TeamMemberData> $team_members
 * @property int $team_members_count
 * @property \Lettermint\Objects\MessageStatsData|mixed $last_28_days
 * @property string $created_at
 * @property string $updated_at
 */
final class ProjectData extends Resource
{
    protected static array $casts = [
        'routes' => [\Lettermint\Objects\RouteData::class],
        'domains' => [\Lettermint\Objects\DomainData::class],
        'team_members' => [\Lettermint\Objects\TeamMemberData::class],
    ];
}
