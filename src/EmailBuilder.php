<?php

declare(strict_types=1);

namespace Lettermint;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use JsonSerializable;
use Lettermint\Exceptions\LettermintConfigException;
use Lettermint\Exceptions\LettermintValidationException;
use Lettermint\Internal\MessageValidator;
use Lettermint\Resources\Emails;
use Lettermint\Types\SendMailResponse;

/**
 * An immutable email builder, created by `$lettermint->emails->compose()`.
 *
 * Every setter returns a new builder and leaves the current one unchanged, so
 * a base builder can be kept as a template and reused safely, also in
 * long-running workers. A setter that throws leaves the builder unchanged.
 * `to()`, `cc()`, `bcc()` and `replyTo()` replace their list; `attach()` appends.
 *
 * ```php
 * $welcome = $lettermint->emails->compose()->from('Acme <hello@acme.com>')->subject('Welcome');
 * $welcome->to('jane@example.com')->html('<p>Hi Jane</p>')->send();
 * ```
 *
 * @phpstan-import-type SendMailRequest from \Lettermint\Types\ApiTypes
 * @phpstan-import-type SendMailRequestSettings from \Lettermint\Types\ApiTypes
 * @phpstan-import-type MessageTagInput from \Lettermint\Types\ApiTypes
 * @phpstan-import-type MessageAttachmentInput from \Lettermint\Types\ApiTypes
 */
final class EmailBuilder implements JsonSerializable
{
    /**
     * @param  array<string, mixed>  $message  The message in the API's format.
     */
    private function __construct(
        private readonly Emails $emails,
        private readonly array $message,
    ) {}

    /**
     * @internal Use $lettermint->emails->compose().
     *
     * @param  array<array-key, mixed>  $message
     */
    public static function start(Emails $emails, array $message = []): self
    {
        MessageValidator::message($message);

        return new self($emails, $message === [] ? ['from' => '', 'to' => [], 'subject' => ''] : $message);
    }

    /**
     * Sender, for example `Acme <hello@acme.com>`.
     */
    public function from(string $address): self
    {
        return $this->with(['from' => $address]);
    }

    /**
     * Replaces the recipients.
     */
    public function to(string ...$addresses): self
    {
        return $this->with(['to' => array_values($addresses)]);
    }

    /**
     * Replaces the CC recipients.
     */
    public function cc(string ...$addresses): self
    {
        return $this->with(['cc' => array_values($addresses)]);
    }

    /**
     * Replaces the BCC recipients.
     */
    public function bcc(string ...$addresses): self
    {
        return $this->with(['bcc' => array_values($addresses)]);
    }

    /**
     * Replaces the Reply-To addresses.
     */
    public function replyTo(string ...$addresses): self
    {
        return $this->with(['reply_to' => array_values($addresses)]);
    }

    public function subject(string $subject): self
    {
        return $this->with(['subject' => $subject]);
    }

    /**
     * HTML body. `null` removes it.
     */
    public function html(?string $html): self
    {
        return $this->with(['html' => $html]);
    }

    /**
     * Plain-text body. `null` removes it.
     */
    public function text(?string $text): self
    {
        return $this->with(['text' => $text]);
    }

    /**
     * Replaces the custom email headers.
     *
     * @param  array<string, string>  $headers
     */
    public function headers(array $headers): self
    {
        return $this->with(['headers' => $headers]);
    }

    /**
     * Replaces the metadata (stored with the message, not added as headers).
     *
     * @param  array<string, string>  $metadata
     */
    public function metadata(array $metadata): self
    {
        return $this->with(['metadata' => $metadata]);
    }

    /**
     * The legacy single tag. `null` removes it.
     */
    public function tag(?string $tag): self
    {
        return $this->with(['tag' => $tag]);
    }

    /**
     * Replaces the name/value tags: up to 20, or 19 with a legacy tag().
     *
     * @param  list<MessageTagInput>  $tags  For example `[['name' => 'campaign', 'value' => 'welcome']]`.
     */
    public function tags(array $tags): self
    {
        return $this->with(['tags' => $tags]);
    }

    /**
     * The route slug to send through.
     */
    public function route(string $route): self
    {
        return $this->with(['route' => $route]);
    }

    /**
     * Schedules delivery: a date, ISO 8601, or English such as `tomorrow 9am`.
     * Dates are sent as ISO 8601 in UTC. `null` removes the schedule.
     */
    public function scheduledAt(DateTimeInterface|string|null $when): self
    {
        if ($when instanceof DateTimeInterface) {
            $when = DateTimeImmutable::createFromInterface($when)->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.v\Z');
        }

        return $this->with(['scheduled_at' => $when]);
    }

    /**
     * Per-email settings that override the route settings.
     *
     * @param  SendMailRequestSettings  $settings  For example `['track_opens' => false, 'tls' => 'enforced']`.
     */
    public function settings(array $settings): self
    {
        return $this->with(['settings' => $settings]);
    }

    /**
     * The result a Sandbox project simulates for every recipient. See Types\SandboxResult.
     */
    public function sandboxResult(string $result): self
    {
        return $this->with(['sandbox_result' => $result]);
    }

    /**
     * Adds an attachment: an Attachment, or an array in the API's format with
     * base64 content.
     *
     * @param  Attachment|MessageAttachmentInput  $attachment
     */
    public function attach(Attachment|array $attachment): self
    {
        $attachments = $this->message['attachments'] ?? [];
        $attachments = is_array($attachments) ? $attachments : [];
        $attachments[] = $attachment instanceof Attachment ? $attachment->toArray() : $attachment;

        return $this->with(['attachments' => $attachments]);
    }

    /**
     * The message in the API's format.
     *
     * @return SendMailRequest
     */
    public function build(): array
    {
        /** @var SendMailRequest */
        return $this->message;
    }

    /**
     * Sends this email. The builder stays unchanged and can be sent again.
     *
     * @param  string|null  $idempotencyKey  Sent as the `Idempotency-Key` header of this call only.
     */
    public function send(?string $idempotencyKey = null): SendMailResponse
    {
        return $this->emails->send($this->build(), $idempotencyKey);
    }

    /**
     * @return SendMailRequest
     */
    public function jsonSerialize(): array
    {
        return $this->build();
    }

    /**
     * Shows the message; no credentials.
     *
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return ['message' => $this->message];
    }

    /**
     * @return never
     */
    public function __serialize(): array
    {
        throw new LettermintConfigException('An EmailBuilder cannot be serialized, because its client holds API tokens. Serialize $builder->build() instead.');
    }

    /**
     * A new builder with `$changes` applied; `null` removes a field. Validates
     * the result, so a rejected change leaves this builder untouched.
     *
     * @param  array<string, mixed>  $changes
     *
     * @throws LettermintValidationException
     */
    private function with(array $changes): self
    {
        $message = $this->message;
        foreach ($changes as $field => $value) {
            if ($value === null) {
                unset($message[$field]);
            } else {
                $message[$field] = $value;
            }
        }
        MessageValidator::message($message);

        return new self($this->emails, $message);
    }
}
