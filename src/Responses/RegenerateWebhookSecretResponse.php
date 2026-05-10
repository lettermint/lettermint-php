<?php

namespace Lettermint\Responses;

use Lettermint\Resource;

/**
 * @property \Lettermint\Objects\WebhookData $data
 * @property string $message
 */
final class RegenerateWebhookSecretResponse extends Resource
{
    protected static array $casts = [
        'data' => \Lettermint\Objects\WebhookData::class,
    ];
}
