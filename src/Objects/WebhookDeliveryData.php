<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $id
 * @property string $webhook_id
 * @property 'message.created'|'message.sent'|'message.delivered'|'message.hard_bounced'|'message.soft_bounced'|'message.spam_complaint'|'message.failed'|'message.suppressed'|'message.unsubscribed'|'message.opened'|'message.clicked'|'message.inbound'|'message.policy_rejected'|'webhook.test' $event_type
 * @property 'pending'|'success'|'failed'|'client_error'|'server_error'|'timeout' $status
 * @property int $attempt_number
 * @property int|null $http_status_code
 * @property int|null $duration_ms
 * @property list<string> $payload
 * @property string|null $response_body
 * @property list<string>|null $response_headers
 * @property string|null $error_message
 * @property string|null $delivered_at
 * @property string $timestamp
 */
final class WebhookDeliveryData extends Resource
{
    //
}
