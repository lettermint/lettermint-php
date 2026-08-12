<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property int $sent
 * @property int $delivered
 * @property int $hard_bounced
 * @property int $spam_complaints
 * @property int|null $opened
 * @property int|null $clicked
 * @property StatsInboundData $inbound
 * @property StatsTypeData|mixed $transactional
 * @property StatsTypeData|mixed $broadcast
 * @property int|null $observed_opened
 * @property int|null $human_opened
 * @property int|null $privacy_opened
 * @property int|null $effective_opened
 * @property int|null $machine_opened
 * @property int|null $machine_clicked
 */
final class StatsTotalsData extends Resource
{
    protected static array $casts = [
        'inbound' => StatsInboundData::class,
    ];
}
