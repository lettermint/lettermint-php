<?php

namespace Lettermint\Responses;

use Lettermint\Resource;

/**
 * @property string $message_id
 * @property 'pending'|'queued'|'suppressed'|'processed'|'delivered'|'opened'|'clicked'|'soft_bounced'|'hard_bounced'|'spam_complaint'|'failed'|'blocked'|'policy_rejected'|'unsubscribed' $status
 */
final class SendMailResponse extends Resource
{
    //
}
