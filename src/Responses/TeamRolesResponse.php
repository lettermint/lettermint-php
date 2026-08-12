<?php

namespace Lettermint\Responses;

use Lettermint\Objects\TeamRoleData;
use Lettermint\Resource;

/**
 * @property list<TeamRoleData> $data
 */
final class TeamRolesResponse extends Resource
{
    protected static array $casts = [
        'data' => [TeamRoleData::class],
    ];
}
