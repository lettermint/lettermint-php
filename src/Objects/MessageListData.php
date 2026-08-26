<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $id
 * @property 'inbound'|'outbound' $type
 * @property 'scheduled'|'pending'|'queued'|'suppressed'|'processed'|'delivered'|'opened'|'clicked'|'soft_bounced'|'hard_bounced'|'spam_complaint'|'failed'|'blocked'|'policy_rejected'|'unsubscribed'|'canceled' $status
 * @property string|null $scheduled_at
 * @property float|int|null $spam_score
 * @property string $from_email
 * @property string|null $from_name
 * @property string|null $subject
 * @property list<MessageRecipientData>|null $to
 * @property list<MessageRecipientData>|null $cc
 * @property list<MessageRecipientData>|null $bcc
 * @property list<string>|null $reply_to
 * @property string|null $tag
 * @property string|null $status_changed_at
 * @property string $created_at
 */
final class MessageListData extends Resource
{
    //
}
