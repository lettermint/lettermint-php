<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $id
 * @property 'inbound'|'outbound' $type
 * @property 'scheduled'|'pending'|'queued'|'quarantined'|'suppressed'|'processed'|'delivered'|'opened'|'clicked'|'soft_bounced'|'hard_bounced'|'spam_complaint'|'failed'|'blocked'|'policy_rejected'|'unsubscribed'|'canceled' $status
 * @property string|null $scheduled_at
 * @property float|int|null $spam_score
 * @property string $from_email
 * @property string|null $from_name
 * @property string|null $subject
 * @property list<\Lettermint\Objects\MessageRecipientData>|null $to
 * @property list<\Lettermint\Objects\MessageRecipientData>|null $cc
 * @property list<\Lettermint\Objects\MessageRecipientData>|null $bcc
 * @property list<string>|null $reply_to
 * @property string|null $tag
 * @property list<array<string, mixed>> $tags
 * @property string|null $status_changed_at
 * @property string $created_at
 * @property 'live'|'sandbox' $delivery_mode
 * @property 'delivered'|'hard_bounced'|'soft_bounced'|'deferred'|'failed'|'suppressed'|'spam_complaint'|'auto_replied'|'opened'|'clicked'|'unsubscribed'|null $sandbox_result
 */
final class MessageListData extends Resource
{
    //
}
