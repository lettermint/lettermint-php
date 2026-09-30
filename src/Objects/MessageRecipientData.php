<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $email
 * @property string|null $name
 * @property 'delivered'|'hard_bounced'|'soft_bounced'|'deferred'|'failed'|'suppressed'|'spam_complaint'|'auto_replied'|'opened'|'clicked'|'unsubscribed'|null $sandbox_result
 */
final class MessageRecipientData extends Resource
{
    //
}
