<?php

namespace Lettermint\Responses;

use Lettermint\Resource;

/**
 * @property \Lettermint\Objects\WebhookData $data
 * @property string $message
 */
final class UpdateWebhookResponse extends Resource
{
    protected static array $casts = [
        'data' => \Lettermint\Objects\WebhookData::class,
    ];
}
