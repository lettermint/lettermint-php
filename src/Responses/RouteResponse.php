<?php

namespace Lettermint\Responses;

use Lettermint\Objects\ProjectData;
use Lettermint\Objects\RouteStatisticData;
use Lettermint\Resource;

/**
 * @property string $id
 * @property string $project_id
 * @property string $slug
 * @property string $name
 * @property 'transactional'|'broadcast'|'inbound' $route_type
 * @property bool $is_default
 * @property string|null $inbound_address
 * @property string|null $inbound_domain
 * @property string|null $inbound_domain_verified_at
 * @property float|int|null $inbound_spam_threshold
 * @property 'inline'|'url' $attachment_delivery
 * @property array<string, mixed>|mixed $settings
 * @property ProjectData $project
 * @property int $webhooks_count
 * @property int $suppressed_recipients_count
 * @property array<string, mixed>|list<RouteStatisticData> $statistics
 * @property string $created_at
 * @property string $updated_at
 */
final class RouteResponse extends Resource
{
    //
}
