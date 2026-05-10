<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $id
 * @property string $route_id
 * @property string $name
 * @property string $url
 * @property list<'message.created'|'message.sent'|'message.delivered'|'message.hard_bounced'|'message.soft_bounced'|'message.spam_complaint'|'message.failed'|'message.suppressed'|'message.unsubscribed'|'message.opened'|'message.clicked'|'message.inbound'|'message.policy_rejected'|'webhook.test'> $events
 * @property bool $enabled
 * @property string|null $last_called_at
 * @property string $created_at
 * @property string $updated_at
 */
final class WebhookListData extends Resource
{
    //
}
