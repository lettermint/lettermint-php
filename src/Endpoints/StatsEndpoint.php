<?php

namespace Lettermint\Endpoints;

use Lettermint\Responses\StatsResponse;

/**
 * @phpstan-import-type StatsQuery from \Lettermint\Types\ApiTypes
 */
class StatsEndpoint extends Endpoint
{
    /**
     * @phpstan-param StatsQuery $query
     */
    public function retrieve(array $query): StatsResponse
    {
        return $this->hydrate(StatsResponse::class, $this->getArray($this->path('/stats'), $query));
    }
}
