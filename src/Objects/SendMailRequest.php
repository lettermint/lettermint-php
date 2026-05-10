<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $route
 * @property string $from
 * @property string $subject
 * @property string|null $tag
 * @property string|null $html
 * @property string|null $text
 * @property list<string> $to
 * @property list<string> $cc
 * @property list<string> $bcc
 * @property list<string> $reply_to
 * @property array<string, string> $headers
 * @property array<string, string> $metadata
 * @property array<string, mixed>|null $settings
 * @property list<array<string, mixed>> $attachments
 */
final class SendMailRequest extends Resource
{
    //
}
