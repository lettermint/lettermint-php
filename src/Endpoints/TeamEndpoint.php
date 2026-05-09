<?php

namespace Lettermint\Endpoints;

/**
 * @phpstan-import-type CursorPage from \Lettermint\Types\ApiTypes
 * @phpstan-import-type ApiObject from \Lettermint\Types\ApiTypes
 * @phpstan-import-type UpdateTeamData from \Lettermint\Types\ApiTypes
 */
class TeamEndpoint extends Endpoint
{
    /** @phpstan-return ApiObject */
    public function retrieve(array $query = []): array
    {
        return $this->getArray($this->path('/team'), $query);
    }

    /**
     * @phpstan-param UpdateTeamData $data
     *
     * @phpstan-return ApiObject
     */
    public function update(array $data): array
    {
        return $this->putArray($this->path('/team'), $data, []);
    }

    /** @phpstan-return ApiObject */
    public function usage(): array
    {
        return $this->getArray($this->path('/team/usage'), []);
    }

    /** @phpstan-return CursorPage */
    public function members(array $query = []): array
    {
        return $this->getArray($this->path('/team/members'), $query);
    }
}
