<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $id
 * @property string $name
 * @property bool $smtp_enabled
 * @property int $routes_count
 * @property int $domains_count
 * @property MessageStatsData $last_28_days
 * @property string $created_at
 * @property string $updated_at
 */
final class ProjectListData extends Resource
{
    protected static array $casts = [
        'last_28_days' => MessageStatsData::class,
    ];
}
