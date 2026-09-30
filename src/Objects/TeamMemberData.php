<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property array<string, mixed> $role
 * @property \Lettermint\Objects\TeamMemberProjectAccessData $project_access
 * @property string|null $joined_at
 */
final class TeamMemberData extends Resource
{
    protected static array $casts = [
        'project_access' => \Lettermint\Objects\TeamMemberProjectAccessData::class,
    ];
}
