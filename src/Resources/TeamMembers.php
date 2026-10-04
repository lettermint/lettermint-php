<?php

declare(strict_types=1);

namespace Lettermint\Resources;

use Generator;
use Lettermint\Types\ListTeamMembersResponse;
use Lettermint\Types\TeamMemberData;

/**
 * Team members. Needs the team token.
 *
 * @phpstan-import-type ListTeamMembersQuery from \Lettermint\Types\ApiTypes
 * @phpstan-import-type UpdateTeamMemberAssignmentData from \Lettermint\Types\ApiTypes
 */
final class TeamMembers extends Resource
{
    /**
     * Lists team members, one page at a time.
     *
     * @param  ListTeamMembersQuery  $query
     */
    public function list(array $query = []): ListTeamMembersResponse
    {
        return $this->transport->object(ListTeamMembersResponse::class, 'GET /team/members', 'team.members.list', query: $query);
    }

    /**
     * Iterates over every team member, following `next_cursor`.
     *
     * @param  ListTeamMembersQuery  $query
     * @return Generator<int, TeamMemberData, mixed, void>
     */
    public function iterate(array $query = []): Generator
    {
        return $this->transport->paginate(ListTeamMembersResponse::class, 'GET /team/members', 'team.members.iterate', query: $query);
    }

    public function retrieve(string $userId): TeamMemberData
    {
        return $this->transport->object(TeamMemberData::class, 'GET /team/members/{userId}', 'team.members.retrieve', ['userId' => $userId]);
    }

    /**
     * Changes a member's role and project access.
     *
     * @param  UpdateTeamMemberAssignmentData  $body
     */
    public function updateAssignment(string $userId, array $body): TeamMemberData
    {
        return $this->transport->object(TeamMemberData::class, 'PUT /team/members/{userId}/assignment', 'team.members.updateAssignment', ['userId' => $userId], json: $body);
    }
}
