<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $message_id
 * @property 'queued'|'processed'|'suppressed'|'delivered'|'soft_bounced'|'hard_bounced'|'spam_complaint'|'failed'|'blocked'|'policy_rejected'|'unsubscribed'|'opened'|'clicked'|'inbound_received'|'inbound_queued'|'inbound_spam_blocked'|'inbound_processed'|'inbound_retry' $event
 * @property array<string, mixed>|null $metadata
 * @property string $timestamp
 */
final class MessageEventData extends Resource
{
    //
}
