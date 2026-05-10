<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $date
 * @property int $sent_count
 * @property int $delivered_count
 * @property int $opened_count
 * @property int $clicked_count
 * @property int $hard_bounce_count
 * @property int $spam_complaint_count
 * @property int $inbound_received_count
 * @property int|null $effective_opened_count
 * @property int|null $machine_opened_count
 * @property int|null $machine_clicked_count
 */
final class RouteStatisticData extends Resource
{
    //
}
