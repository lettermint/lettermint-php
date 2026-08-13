<?php

namespace Lettermint\Endpoints;

use Lettermint\Responses\TeamMembersAssignmentUpdateResponse;
use Lettermint\Responses\TeamMembersResponse;
use Lettermint\Responses\TeamMembersShowResponse;
use Lettermint\Responses\TeamResponse;
use Lettermint\Responses\TeamRolesResponse;
use Lettermint\Responses\TeamUsageResponse;
use Lettermint\Responses\UpdateTeamResponse;

/**
 * @phpstan-import-type UpdateTeamData from \Lettermint\Types\ApiTypes
 * @phpstan-import-type UpdateTeamMemberAssignmentData from \Lettermint\Types\ApiTypes
 */
class TeamEndpoint extends Endpoint
{
    public function retrieve(array $query = []): TeamResponse
    {
        return $this->hydrate(TeamResponse::class, $this->getArray($this->path('/team'), $query));
    }

    /**
     * @phpstan-param UpdateTeamData $data
     */
    public function update(array $data): UpdateTeamResponse
    {
        return $this->hydrate(UpdateTeamResponse::class, $this->putArray($this->path('/team'), $data, []));
    }

    public function usage(): TeamUsageResponse
    {
        return $this->hydrate(TeamUsageResponse::class, $this->getArray($this->path('/team/usage'), []));
    }

    public function roles(): TeamRolesResponse
    {
        return $this->hydrate(TeamRolesResponse::class, $this->getArray($this->path('/team/roles'), []));
    }

    public function members(array $query = []): TeamMembersResponse
    {
        return $this->hydrate(TeamMembersResponse::class, $this->getArray($this->path('/team/members'), $query));
    }

    public function member(string $userId): TeamMembersShowResponse
    {
        return $this->hydrate(TeamMembersShowResponse::class, $this->getArray($this->path('/team/members/{userId}', ['userId' => $userId]), []));
    }

    /**
     * @phpstan-param UpdateTeamMemberAssignmentData $data
     */
    public function updateMemberAssignment(string $userId, array $data): TeamMembersAssignmentUpdateResponse
    {
        return $this->hydrate(TeamMembersAssignmentUpdateResponse::class, $this->putArray($this->path('/team/members/{userId}/assignment', ['userId' => $userId]), $data, []));
    }
}
