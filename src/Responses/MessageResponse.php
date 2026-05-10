<?php

namespace Lettermint\Responses;

use Lettermint\Resource;

/**
 * @property string $id
 * @property 'inbound'|'outbound' $type
 * @property 'pending'|'queued'|'suppressed'|'processed'|'delivered'|'opened'|'clicked'|'soft_bounced'|'hard_bounced'|'spam_complaint'|'failed'|'blocked'|'policy_rejected'|'unsubscribed' $status
 * @property string|null $status_changed_at
 * @property string|null $tag
 * @property string $from_email
 * @property string|null $from_name
 * @property list<string>|null $reply_to
 * @property string|null $subject
 * @property list<\Lettermint\Objects\MessageRecipientData>|null $to
 * @property list<\Lettermint\Objects\MessageRecipientData>|null $cc
 * @property list<\Lettermint\Objects\MessageRecipientData>|null $bcc
 * @property list<\Lettermint\Objects\MessageAttachmentData>|null $attachments
 * @property array<string, string>|null $metadata
 * @property float|int|null $spam_score
 * @property list<\Lettermint\Objects\SpamSymbol> $spam_symbols
 * @property string $route_id
 * @property string $created_at
 */
final class MessageResponse extends Resource
{
    //
}
