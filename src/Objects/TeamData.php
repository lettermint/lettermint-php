<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $id
 * @property string $name
 * @property 'personal'|'business' $type
 * @property 'free'|'starter'|'growth'|'pro' $plan
 * @property 300|10000|50000|125000|500000|750000|1000000|1500000 $tier
 * @property string|null $verified_at
 * @property list<string> $features
 * @property list<\Lettermint\Objects\TeamAddonData> $addons
 * @property string $created_at
 * @property int $domains_count
 * @property int $projects_count
 * @property int $members_count
 */
final class TeamData extends Resource
{
    protected static array $casts = [
        'addons' => [\Lettermint\Objects\TeamAddonData::class],
    ];
}
