<?php

namespace Lettermint\Responses;

use Lettermint\Resource;

/**
 * @property list<\Lettermint\Objects\WebhookDeliveryListData> $data
 * @property string|null $path
 * @property int $per_page
 * @property string|null $next_cursor
 * @property string|null $next_page_url
 * @property string|null $prev_cursor
 * @property string|null $prev_page_url
 */
final class WebhookDeliveriesResponse extends Resource
{
    protected static array $casts = [
        'data' => [\Lettermint\Objects\WebhookDeliveryListData::class],
    ];
}
