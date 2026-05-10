<?php

namespace Lettermint\Endpoints;

use Lettermint\Responses\TeamMembersResponse;
use Lettermint\Responses\TeamResponse;
use Lettermint\Responses\TeamUsageResponse;
use Lettermint\Responses\UpdateTeamResponse;

/**
 * @phpstan-import-type UpdateTeamData from \Lettermint\Types\ApiTypes
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

    public function members(array $query = []): TeamMembersResponse
    {
        return $this->hydrate(TeamMembersResponse::class, $this->getArray($this->path('/team/members'), $query));
    }
}
