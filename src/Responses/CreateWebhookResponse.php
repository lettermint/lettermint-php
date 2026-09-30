<?php

namespace Lettermint\Responses;

use Lettermint\Resource;

/**
 * @property \Lettermint\Objects\WebhookSecretData $data
 * @property string $message
 */
final class CreateWebhookResponse extends Resource
{
    protected static array $casts = [
        'data' => \Lettermint\Objects\WebhookSecretData::class,
    ];
}
