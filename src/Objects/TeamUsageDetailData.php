<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property \Lettermint\Objects\TeamUsagePeriodData $current_period
 * @property list<\Lettermint\Objects\TeamUsagePeriodData> $historical_usage
 */
final class TeamUsageDetailData extends Resource
{
    protected static array $casts = [
        'current_period' => \Lettermint\Objects\TeamUsagePeriodData::class,
        'historical_usage' => [\Lettermint\Objects\TeamUsagePeriodData::class],
    ];
}
