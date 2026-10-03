<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $id
 * @property 'team'|'project'|'route' $scope
 * @property list<string> $project_ids
 * @property list<string> $route_ids
 * @property string|null $route_id
 * @property string $name
 * @property string $url
 * @property bool $has_basic_auth
 * @property list<string> $events
 * @property bool $enabled
 * @property bool $include_machine_events
 * @property string $secret
 * @property string|null $last_called_at
 * @property string $created_at
 * @property string $updated_at
 * @property 'live'|'sandbox'|'both' $delivery_mode_filter
 */
final class WebhookSecretData extends Resource
{
    //
}
