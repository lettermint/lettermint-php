<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string|null $name
 * @property \Lettermint\Objects\UpdateRouteSettingsData|null $settings
 * @property \Lettermint\Objects\UpdateRouteInboundSettingsData|null $inbound_settings
 * @property string|null $inbound_domain
 * @property float|int|null $inbound_spam_threshold
 * @property 'inline'|'url'|null $attachment_delivery
 */
final class UpdateRouteData extends Resource
{
    //
}
