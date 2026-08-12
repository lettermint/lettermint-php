<?php

namespace Lettermint\Responses;

use Lettermint\Objects\TeamMemberProjectAccessData;
use Lettermint\Resource;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property array<string, mixed> $role
 * @property TeamMemberProjectAccessData $project_access
 * @property string|null $joined_at
 */
final class TeamMembersAssignmentUpdateResponse extends Resource
{
    protected static array $casts = [
        'project_access' => TeamMemberProjectAccessData::class,
    ];
}
