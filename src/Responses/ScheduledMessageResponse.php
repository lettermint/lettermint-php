<?php

namespace Lettermint\Responses;

use Lettermint\Resource;

/**
 * @property string $message_id
 * @property 'scheduled'|'pending'|'queued'|'suppressed'|'processed'|'delivered'|'opened'|'clicked'|'soft_bounced'|'hard_bounced'|'spam_complaint'|'failed'|'blocked'|'policy_rejected'|'unsubscribed'|'canceled'|null $status
 * @property string|null $scheduled_at
 */
final class ScheduledMessageResponse extends Resource
{
    //
}
