<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $name
 * @property bool $smtp_enabled
 * @property 'both'|'transactional'|'broadcast' $initial_routes
 * @property bool $short_token
 * @property 'live'|'sandbox' $delivery_mode
 */
final class StoreProjectData extends Resource
{
    //
}
