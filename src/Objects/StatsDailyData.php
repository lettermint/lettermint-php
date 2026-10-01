<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $date
 * @property int $sent
 * @property int $delivered
 * @property int $hard_bounced
 * @property int $spam_complaints
 * @property int|null $opened
 * @property int|null $clicked
 * @property StatsInboundData $inbound
 * @property StatsTypeData|null $transactional
 * @property StatsTypeData|null $broadcast
 * @property int|null $observed_opened
 * @property int|null $human_opened
 * @property int|null $privacy_opened
 * @property int|null $effective_opened
 * @property int|null $machine_opened
 * @property int|null $machine_clicked
 */
final class StatsDailyData extends Resource
{
    protected static array $casts = [
        'transactional' => StatsTypeData::class,
        'broadcast' => StatsTypeData::class,
        'inbound' => StatsInboundData::class,
    ];
}
