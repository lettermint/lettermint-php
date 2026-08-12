<?php

namespace Lettermint\Responses;

use Lettermint\Objects\TeamAddonData;
use Lettermint\Resource;

/**
 * @property string $id
 * @property string $name
 * @property 'personal'|'business' $type
 * @property 'free'|'starter'|'growth'|'pro' $plan
 * @property int $included_volume
 * @property int $tier
 * @property string|null $verified_at
 * @property list<string> $features
 * @property list<TeamAddonData> $addons
 * @property string $created_at
 * @property int $domains_count
 * @property int $projects_count
 * @property int $members_count
 */
final class TeamResponse extends Resource
{
    //
}
