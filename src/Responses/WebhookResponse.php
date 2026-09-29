<?php

namespace Lettermint\Responses;

use Lettermint\Resource;

/**
 * @property string $id
 * @property 'team'|'project'|'route' $scope
 * @property list<string> $project_ids
 * @property list<string> $route_ids
 * @property string|null $route_id
 * @property string $name
 * @property string $url
 * @property list<string> $events
 * @property bool $enabled
 * @property bool $include_machine_events
 * @property string|null $last_called_at
 * @property string $created_at
 * @property string $updated_at
 * @property 'live'|'sandbox'|'both' $delivery_mode_filter
 */
final class WebhookResponse extends Resource
{
    //
}
