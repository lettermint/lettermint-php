<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $id
 * @property string $slug
 * @property string $name
 * @property 'transactional'|'broadcast'|'inbound' $route_type
 * @property bool $is_default
 * @property int $webhooks_count
 * @property int $suppressed_recipients_count
 * @property string $created_at
 * @property string $updated_at
 */
final class RouteListData extends Resource
{
    //
}
