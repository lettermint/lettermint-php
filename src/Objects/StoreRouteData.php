<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $name
 * @property 'transactional'|'broadcast'|'inbound' $route_type
 * @property string|null $slug
 * @property \Lettermint\Objects\UpdateRouteSettingsData|null $settings
 * @property \Lettermint\Objects\UpdateRouteInboundSettingsData|null $inbound_settings
 * @property string|null $inbound_domain
 * @property float|int|null $inbound_spam_threshold
 * @property 'inline'|'url'|null $attachment_delivery
 */
final class StoreRouteData extends Resource
{
    //
}
