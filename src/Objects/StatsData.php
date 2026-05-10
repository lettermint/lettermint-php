<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $from
 * @property string $to
 * @property \Lettermint\Objects\StatsTotalsData $totals
 * @property list<\Lettermint\Objects\StatsDailyData> $daily
 */
final class StatsData extends Resource
{
    protected static array $casts = [
        'totals' => \Lettermint\Objects\StatsTotalsData::class,
        'daily' => [\Lettermint\Objects\StatsDailyData::class],
    ];
}
