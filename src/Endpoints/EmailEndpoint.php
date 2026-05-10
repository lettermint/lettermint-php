<?php

namespace Lettermint\Endpoints;

use Lettermint\Responses\SendBatchMailResponse;
use Lettermint\Responses\SendMailResponse;

/**
 * @phpstan-import-type SendMailRequest from \Lettermint\Types\ApiTypes
 * @phpstan-import-type SendBatchMailRequest from \Lettermint\Types\ApiTypes
 *
 * @phpstan-type AttachmentPayload array{
 *     filename: string,
 *     content: string,
 *     content_type?: string,
 *     content_id?: string
 * }
 * @phpstan-type EmailSettings array{
 *     track_opens?: bool,
 *     track_clicks?: bool
 * }
 * @phpstan-type EmailPayload array{
 *     from?: string,
 *     to?: list<string>,
 *     subject?: string,
 *     html?: string|null,
 *     text?: string|null,
 *     cc?: list<string>,
 *     bcc?: list<string>,
 *     reply_to?: list<string>,
 *     attachments?: list<AttachmentPayload>,
 *     route?: string,
 *     metadata?: array<string, string>,
 *     tag?: string|null,
 *     settings?: EmailSettings|null,
 *     headers?: array<string, string>
 * }
 */
class EmailEndpoint extends Endpoint
{
    /**
     * @var EmailPayload The email payload to be sent.
     */
    private array $payload = [];

    /**
     * @var string|null The idempotency key for the request.
     */
    private ?string $idempotencyKey = null;

    /**
     * Set the custom headers.
     *
     * @example ["Key" => "Value"]
     *
     * @param  array<string, string>  $headers  The custom headers.
     */
    public function headers(array $headers): self
    {
        $this->payload['headers'] = $headers;

        return $this;
    }

    /**
     * Set the sender email address.
     *
     * Supports RFC 5322 addresses, e.g. <EMAIL>, <NAME> <<EMAIL>>.
     *
     * @example John Doe <john@acme.com>
     * @example john@acme.com
     *
     * @param  string  $email  The sender's email address.
     */
    public function from(string $email): self
    {
        $this->payload['from'] = $email;

        return $this;
    }

    /**
     * Set one or more recipient email addresses.
     *
     * @param  string  ...$emails  One or more recipient email addresses.
     */
    public function to(string ...$emails): self
    {
        $this->payload['to'] = $emails;

        return $this;
    }

    /**
     * Set the subject of the email.
     *
     * @param  string  $subject  The subject line.
     */
    public function subject(string $subject): self
    {
        $this->payload['subject'] = $subject;

        return $this;
    }

    /**
     * Set the HTML body of the email.
     *
     * @param  string|null  $html  The HTML content for the email body.
     */
    public function html(?string $html): self
    {
        $this->payload['html'] = $html;

        return $this;
    }

    /**
     * Set the plain text body of the email.
     *
     * @param  string|null  $text  The plain text content for the email body.
     */
    public function text(?string $text): self
    {
        $this->payload['text'] = $text;

        return $this;
    }

    /**
     * Set one or more CC email addresses.
     *
     * @param  string  ...$emails  Email addresses to be CC'd.
     */
    public function cc(string ...$emails): self
    {
        $this->payload['cc'] = $emails;

        return $this;
    }

    /**
     * Set one or more BCC email addresses.
     *
     * @param  string  ...$emails  Email addresses to be BCC'd.
     */
    public function bcc(string ...$emails): self
    {
        $this->payload['bcc'] = $emails;

        return $this;
    }

    /**
     * Set one or more Reply-To email addresses.
     *
     * @param  string  ...$emails  Reply-To email addresses.
     */
    public function replyTo(string ...$emails): self
    {
        $this->payload['reply_to'] = $emails;

        return $this;
    }

    /**
     * Attach a file to the email.
     *
     * @param  string  $filename  The attachment filename.
     * @param  string  $base64Content  The base64-encoded file content.
     * @param  string|null  $contentId  Optional content ID for inline attachments.
     * @param  string|null  $contentType  Optional MIME type for the attachment.
     */
    public function attach(string $filename, string $base64Content, ?string $contentId = null, ?string $contentType = null): self
    {
        /** @var AttachmentPayload $attachment */
        $attachment = [
            'filename' => $filename,
            'content' => $base64Content,
        ];

        if ($contentType !== null) {
            $attachment['content_type'] = $contentType;
        }

        if ($contentId !== null) {
            $attachment['content_id'] = $contentId;
        }

        $this->payload['attachments'][] = $attachment;

        return $this;
    }

    /**
     * Set the route id for the email.
     *
     * @param  string  $route  The route id to use for sending.
     */
    public function route(string $route): self
    {
        $this->payload['route'] = $route;

        return $this;
    }

    /**
     * Set the idempotency key for the request.
     *
     * @param  string  $key  The idempotency key to ensure request uniqueness.
     */
    public function idempotencyKey(string $key): self
    {
        $this->idempotencyKey = $key;

        return $this;
    }

    /**
     * Set custom metadata.
     *
     * @example ["key" => "value"]
     *
     * @param  array<string, string>  $metadata  The metadata object.
     */
    public function metadata(array $metadata): self
    {
        $this->payload['metadata'] = $metadata;

        return $this;
    }

    /**
     * Set the tag of the email.
     *
     * @param  string|null  $tag  The tag formatted as slug
     */
    public function tag(?string $tag): self
    {
        $this->payload['tag'] = $tag;

        return $this;
    }

    /**
     * Set per-email settings.
     *
     * @param  EmailSettings  $settings
     */
    public function settings(array $settings): self
    {
        $this->payload['settings'] = $settings;

        return $this;
    }

    /**
     * Ping the Sending API.
     *
     * @throws \Exception On HTTP or API failure.
     */
    public function ping(): int
    {
        return $this->getInt('/v1/ping');
    }

    /**
     * Send multiple emails in a batch.
     *
     * @phpstan-param SendBatchMailRequest $messages
     *
     * @throws \Exception On HTTP or API failure.
     */
    public function sendBatch(array $messages): SendBatchMailResponse
    {
        return $this->hydrateList(SendBatchMailResponse::class, $this->postArray('/v1/send/batch', $messages, []));
    }

    /**
     * Send the composed email using the current payload.
     *
     * @phpstan-param SendMailRequest|null $payload
     *
     * @throws \Exception On HTTP or API failure.
     */
    public function send(?array $payload = null): SendMailResponse
    {
        $headers = [];

        if ($this->idempotencyKey !== null) {
            $headers['Idempotency-Key'] = $this->idempotencyKey;
        }

        $result = $this->postArray('/v1/send', $payload ?? $this->payload, $headers);

        $this->payload = [];
        $this->idempotencyKey = null;

        return $this->hydrate(SendMailResponse::class, $result);
    }
}
