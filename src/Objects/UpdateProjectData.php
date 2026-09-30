<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string|null $name
 * @property bool|null $smtp_enabled
 * @property bool|null $redact_email_content
 * @property string|null $default_route_id
 * @property 'live'|'sandbox'|null $delivery_mode
 */
final class UpdateProjectData extends Resource
{
    //
}
