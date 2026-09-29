<?php

namespace Lettermint\Responses;

use Lettermint\Resource;

/**
 * @property string|null $message_id
 * @property 'scheduled'|'pending'|'queued'|'quarantined'|'suppressed'|'processed'|'delivered'|'opened'|'clicked'|'soft_bounced'|'hard_bounced'|'spam_complaint'|'failed'|'blocked'|'policy_rejected'|'unsubscribed'|'canceled' $status
 * @property string $scheduled_at
 * @property bool $sandbox
 * @property 'delivered'|'hard_bounced'|'soft_bounced'|'deferred'|'failed'|'suppressed'|'spam_complaint'|'auto_replied'|'opened'|'clicked'|'unsubscribed' $sandbox_result
 */
final class SendMailResponse extends Resource
{
    //
}
