<?php

namespace Lettermint\Responses;

use Lettermint\Resource;

/**
 * @property string $message_id
 * @property 'pending'|'scheduled' $status
 * @property bool $sandbox
 * @property 'delivered'|'hard_bounced'|'soft_bounced'|'deferred'|'failed'|'suppressed'|'spam_complaint'|'auto_replied'|'opened'|'clicked'|'unsubscribed' $sandbox_result
 * @property string $scheduled_at
 */
final class SendMailResponse extends Resource
{
    //
}
