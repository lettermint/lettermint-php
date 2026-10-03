<?php

declare(strict_types=1);

namespace Lettermint\Resources;

use Lettermint\Types\StatsData;

/**
 * Sending statistics. Needs the team token.
 *
 * @phpstan-import-type GetStatsQuery from \Lettermint\Types\ApiTypes
 */
final class Stats extends Resource
{
    /**
     * Daily statistics between `from` and `to` (Y-m-d, at most 90 days).
     *
     * @param  GetStatsQuery  $query
     */
    public function retrieve(array $query): StatsData
    {
        return $this->transport->object(StatsData::class, 'GET /stats', 'stats.retrieve', query: $query);
    }
}
