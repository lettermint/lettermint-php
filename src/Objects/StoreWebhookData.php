<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $route_id
 * @property string $name
 * @property string $url
 * @property bool|null $enabled
 * @property bool|null $include_machine_events
 * @property list<'message.created'|'message.sent'|'message.delivered'|'message.hard_bounced'|'message.soft_bounced'|'message.spam_complaint'|'message.failed'|'message.suppressed'|'message.unsubscribed'|'message.opened'|'message.clicked'|'message.inbound'|'message.policy_rejected'|'webhook.test'> $events
 */
final class StoreWebhookData extends Resource
{
    //
}
