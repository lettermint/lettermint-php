<?php

declare(strict_types=1);

namespace Lettermint\Resources;

use Lettermint\Internal\Transport;
use Lettermint\Types\MessageResponse;
use Lettermint\Types\TeamData;
use Lettermint\Types\TeamMutationResponse;
use Lettermint\Types\TeamRoleData;
use Lettermint\Types\TeamRoleListResponse;
use Lettermint\Types\TeamUsageDetailData;

/**
 * The team of the token. Needs the team token.
 *
 * @phpstan-import-type GetTeamQuery from \Lettermint\Types\ApiTypes
 * @phpstan-import-type UpdateTeamData from \Lettermint\Types\ApiTypes
 * @phpstan-import-type CreateTeamRoleData from \Lettermint\Types\ApiTypes
 * @phpstan-import-type UpdateTeamRoleData from \Lettermint\Types\ApiTypes
 * @phpstan-import-type DeleteTeamRoleQuery from \Lettermint\Types\ApiTypes
 * @phpstan-import-type DuplicateTeamRoleData from \Lettermint\Types\ApiTypes
 */
final class Team extends Resource
{
    /** Team members. */
    public readonly TeamMembers $members;

    /** Pending team invitations. */
    public readonly TeamInvites $invites;

    /**
     * @internal Use $lettermint->team.
     */
    public function __construct(Transport $transport)
    {
        parent::__construct($transport);
        $this->members = new TeamMembers($transport);
        $this->invites = new TeamInvites($transport);
    }

    /**
     * @param  GetTeamQuery  $query  For example `['include' => ['features']]`.
     */
    public function retrieve(array $query = []): TeamData
    {
        return $this->transport->object(TeamData::class, 'GET /team', 'team.retrieve', query: $query);
    }

    /**
     * @param  UpdateTeamData  $body
     */
    public function update(array $body): TeamMutationResponse
    {
        return $this->transport->object(TeamMutationResponse::class, 'PUT /team', 'team.update', json: $body);
    }

    /**
     * Usage of the current and previous billing periods.
     */
    public function usage(): TeamUsageDetailData
    {
        return $this->transport->object(TeamUsageDetailData::class, 'GET /team/usage', 'team.usage');
    }

    /**
     * The roles that can be assigned to members.
     */
    public function roles(): TeamRoleListResponse
    {
        return $this->transport->object(TeamRoleListResponse::class, 'GET /team/roles', 'team.roles');
    }

    public function retrieveRole(string $roleId): TeamRoleData
    {
        return $this->transport->object(TeamRoleData::class, 'GET /team/roles/{roleId}', 'team.retrieveRole', ['roleId' => $roleId]);
    }

    /**
     * Creates a custom role. Custom roles need a plan that includes them; the
     * permissions must include everything they depend on, and role names are
     * unique within the team.
     *
     * @param  CreateTeamRoleData  $body
     */
    public function createRole(array $body): TeamRoleData
    {
        return $this->transport->object(TeamRoleData::class, 'POST /team/roles', 'team.createRole', json: $body);
    }

    /**
     * Replaces the name, description and permissions of a custom role. Built-in
     * roles cannot be changed.
     *
     * @param  UpdateTeamRoleData  $body
     */
    public function updateRole(string $roleId, array $body): TeamRoleData
    {
        return $this->transport->object(TeamRoleData::class, 'PUT /team/roles/{roleId}', 'team.updateRole', ['roleId' => $roleId], json: $body);
    }

    /**
     * Deletes a custom role. While the role is still assigned to members,
     * invites or SCIM groups, pass the role that receives those assignments.
     * Built-in roles cannot be deleted.
     *
     * @param  DeleteTeamRoleQuery  $query  For example `['replacement_role_id' => 'role-id']`.
     */
    public function deleteRole(string $roleId, array $query = []): MessageResponse
    {
        return $this->transport->object(MessageResponse::class, 'DELETE /team/roles/{roleId}', 'team.deleteRole', ['roleId' => $roleId], query: $query);
    }

    /**
     * Copies a role's permissions to a new custom role. Without a description,
     * the copy keeps the source role's description. The Owner role cannot be
     * duplicated.
     *
     * @param  DuplicateTeamRoleData  $body
     */
    public function duplicateRole(string $roleId, array $body): TeamRoleData
    {
        return $this->transport->object(TeamRoleData::class, 'POST /team/roles/{roleId}/duplicate', 'team.duplicateRole', ['roleId' => $roleId], json: $body);
    }
}
