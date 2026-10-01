<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $route
 * @property string $from
 * @property list<string> $to
 * @property list<string> $cc
 * @property list<string> $bcc
 * @property list<string> $reply_to
 * @property string $subject
 * @property string $scheduled_at
 * @property 'delivered'|'hard_bounced'|'soft_bounced'|'deferred'|'failed'|'suppressed'|'spam_complaint'|'auto_replied'|'opened'|'clicked'|'unsubscribed' $sandbox_result
 * @property array<string, string> $headers
 * @property array<string, string> $metadata
 * @property string|null $tag
 * @property list<array<string, mixed>> $tags
 * @property array<string, mixed> $settings
 * @property string|null $html
 * @property string|null $text
 * @property list<array<string, mixed>> $attachments
 */
final class SendMailRequest extends Resource
{
    //
}
