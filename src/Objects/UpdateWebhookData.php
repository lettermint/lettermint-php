<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $name
 * @property string $url
 * @property WebhookBasicAuthData|null $basic_auth
 * @property list<'message.created'|'message.sent'|'message.delivered'|'message.auto_replied'|'message.hard_bounced'|'message.soft_bounced'|'message.spam_complaint'|'message.failed'|'message.suppressed'|'message.unsubscribed'|'message.opened'|'message.clicked'|'message.inbound'|'message.policy_rejected'|'message.scheduled'|'message.rescheduled'|'message.canceled'|'message.released'|'suppression.added'|'suppression.removed'|'webhook.test'> $events
 * @property bool $enabled
 * @property bool $include_machine_events
 * @property 'team'|'project'|'route' $scope
 * @property list<string> $project_ids
 * @property list<string> $route_ids
 * @property string|null $route_id
 * @property 'live'|'sandbox'|'both' $delivery_mode_filter
 */
final class UpdateWebhookData extends Resource
{
    protected static array $casts = [
        'basic_auth' => WebhookBasicAuthData::class,
    ];
}
