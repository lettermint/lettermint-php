<?php

namespace Lettermint\Endpoints;

/**
 * @phpstan-import-type ApiObject from \Lettermint\Types\ApiTypes
 * @phpstan-import-type StatsQuery from \Lettermint\Types\ApiTypes
 */
class StatsEndpoint extends Endpoint
{
    /**
     * @phpstan-param StatsQuery $query
     *
     * @phpstan-return ApiObject
     */
    public function retrieve(array $query): array
    {
        return $this->getArray($this->path('/stats'), $query);
    }
}
