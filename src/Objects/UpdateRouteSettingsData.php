<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property bool|null $track_opens
 * @property bool|null $track_clicks
 * @property bool|null $generate_plaintext_fallback
 * @property bool|null $suppress_auto_responders
 * @property 'opportunistic'|'enforced'|null $tls
 * @property bool|null $disable_hosted_unsubscribe
 * @property bool|null $redact_email_content
 */
final class UpdateRouteSettingsData extends Resource
{
    //
}
