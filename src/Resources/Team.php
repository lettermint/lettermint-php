<?php

declare(strict_types=1);

namespace Lettermint\Resources;

use Lettermint\Internal\Transport;
use Lettermint\Types\TeamData;
use Lettermint\Types\TeamMutationResponse;
use Lettermint\Types\TeamRoleListResponse;
use Lettermint\Types\TeamUsageDetailData;

/**
 * The team of the token. Needs the team token.
 *
 * @phpstan-import-type GetTeamQuery from \Lettermint\Types\ApiTypes
 * @phpstan-import-type UpdateTeamData from \Lettermint\Types\ApiTypes
 */
final class Team extends Resource
{
    /** Team members. */
    public readonly TeamMembers $members;

    /**
     * @internal Use $lettermint->team.
     */
    public function __construct(Transport $transport)
    {
        parent::__construct($transport);
        $this->members = new TeamMembers($transport);
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
}
