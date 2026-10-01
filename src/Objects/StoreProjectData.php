<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $name
 * @property bool $smtp_enabled
 * @property 'live'|'sandbox' $delivery_mode
 * @property 'both'|'transactional'|'broadcast' $initial_routes
 * @property bool $short_token
 * @property bool $redact_email_content
 */
final class StoreProjectData extends Resource
{
    //
}
