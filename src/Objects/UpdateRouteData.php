<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string|null $name
 * @property UpdateRouteSettingsData|null $settings
 * @property UpdateRouteInboundSettingsData|null $inbound_settings
 * @property string|null $inbound_domain
 * @property float|int|null $inbound_spam_threshold
 * @property 'inline'|'url'|null $attachment_delivery
 */
final class UpdateRouteData extends Resource
{
    protected static array $casts = [
        'settings' => UpdateRouteSettingsData::class,
        'inbound_settings' => UpdateRouteInboundSettingsData::class,
    ];
}
