<?php

declare(strict_types=1);

namespace Lettermint\Internal;

use Lettermint\Exceptions\LettermintValidationException;

/**
 * Checks what the SDK can check before a request: the message shape, tags and
 * attachments. Shared by `emails->send()`, `emails->sendBatch()` and the
 * email builder. The tag rules match the Lettermint API.
 *
 * @internal
 */
final class MessageValidator
{
    private const TAG_NAME = '/\A[A-Za-z0-9_-]{1,32}\z/';

    private const TAG_VALUE = '/\A[A-Za-z0-9_-]{1,64}\z/';

    private const MAX_TAGS = 20;

    private const HEADER_VALUE = '/\A[^\r\n\0]+\z/';

    /**
     * @param  string  $prefix  The field prefix for messages in a batch, e.g. `messages[2]`.
     *
     * @phpstan-assert array<string, mixed> $message
     */
    public static function message(mixed $message, string $prefix = ''): void
    {
        $at = static fn (string $field): string => $prefix === '' ? $field : "{$prefix}.{$field}";
        if (! is_array($message) || ($message !== [] && array_is_list($message))) {
            self::fail($prefix === '' ? 'message' : $prefix, 'An email message must be an array with string keys.');
        }
        $legacyTag = $message['tag'] ?? null;
        if ($legacyTag !== null && ! is_string($legacyTag)) {
            self::fail($at('tag'), 'The legacy tag must be a string.');
        }
        self::tags($message['tags'] ?? null, is_string($legacyTag) && $legacyTag !== '', $at('tags'));
        if (array_key_exists('attachments', $message)) {
            $attachments = $message['attachments'];
            if (! is_array($attachments) || ! array_is_list($attachments)) {
                self::fail($at('attachments'), 'Attachments must be a list.');
            }
            foreach ($attachments as $index => $attachment) {
                $field = $at('attachments')."[{$index}]";
                if (! is_array($attachment) || ! isset($attachment['filename']) || ! is_string($attachment['filename']) || $attachment['filename'] === '') {
                    self::fail($field, 'An attachment needs a filename. Use the builder\'s attach() or Attachment::fromBytes(...)->toArray() for files.');
                }
                if (! isset($attachment['content']) || ! is_string($attachment['content'])) {
                    self::fail($field, 'Attachment content must be a base64-encoded string. Use the builder\'s attach() or Attachment::fromBytes(...)->toArray() for raw bytes.');
                }
            }
        }
    }

    public static function idempotencyKey(?string $key): void
    {
        if ($key !== null && preg_match(self::HEADER_VALUE, $key) !== 1) {
            self::fail('idempotencyKey', 'The idempotency key must be a non-empty string without line breaks.');
        }
    }

    private static function tags(mixed $tags, bool $hasLegacyTag, string $field): void
    {
        if ($tags === null) {
            return;
        }
        if (! is_array($tags) || ! array_is_list($tags)) {
            self::fail($field, 'Message tags must be a list of [name, value] arrays.');
        }
        $maximum = $hasLegacyTag ? self::MAX_TAGS - 1 : self::MAX_TAGS;
        if (count($tags) > $maximum) {
            self::fail($field, $hasLegacyTag
                ? "A legacy tag and no more than {$maximum} message tags are permitted."
                : "No more than {$maximum} message tags are permitted.");
        }
        $names = [];
        foreach ($tags as $tag) {
            if (! is_array($tag) || ! isset($tag['name'], $tag['value']) || ! is_string($tag['name']) || ! is_string($tag['value'])) {
                self::fail($field, 'Message tags must be [\'name\' => ..., \'value\' => ...] arrays with string values.');
            }
            $name = $tag['name'];
            if (preg_match(self::TAG_NAME, $name) !== 1) {
                self::fail($field, 'Message tag names must match ^[A-Za-z0-9_-]{1,32}$.');
            }
            if (str_starts_with(strtolower($name), '__lettermint')) {
                self::fail($field, 'Message tag names must not start with __lettermint.');
            }
            if (preg_match(self::TAG_VALUE, $tag['value']) !== 1) {
                self::fail($field, 'Message tag values must match ^[A-Za-z0-9_-]{1,64}$.');
            }
            if (isset($names[$name])) {
                self::fail($field, 'Message tag names must be unique (case-sensitive).');
            }
            $names[$name] = true;
        }
    }

    private static function fail(string $field, string $message): never
    {
        throw new LettermintValidationException($message, $field);
    }
}
