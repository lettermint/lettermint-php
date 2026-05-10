<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $id
 * @property \Lettermint\Objects\UserData $user
 * @property string|null $role
 * @property string|null $joined_at
 */
final class TeamMemberData extends Resource
{
    protected static array $casts = [
        'user' => \Lettermint\Objects\UserData::class,
    ];
}
