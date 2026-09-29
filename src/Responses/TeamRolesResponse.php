<?php

namespace Lettermint\Responses;

use Lettermint\Resource;

/**
 * @property list<\Lettermint\Objects\TeamRoleData> $data
 */
final class TeamRolesResponse extends Resource
{
    protected static array $casts = [
        'data' => [\Lettermint\Objects\TeamRoleData::class],
    ];
}
