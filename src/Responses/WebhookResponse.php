<?php

namespace Lettermint\Responses;

use Lettermint\Resource;

/**
 * @property string $id
 * @property string $route_id
 * @property string $name
 * @property string $url
 * @property list<string> $events
 * @property bool $enabled
 * @property bool $include_machine_events
 * @property string $secret
 * @property string|null $last_called_at
 * @property string $created_at
 * @property string $updated_at
 */
final class WebhookResponse extends Resource
{
    //
}
