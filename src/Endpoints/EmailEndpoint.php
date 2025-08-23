<?php

namespace Lettermint\Endpoints;

class EmailEndpoint extends Endpoint
{
    /**
     * @var array The email payload to be sent.
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
     */
    public function attach(string $filename, string $base64Content): self
    {
        $this->payload['attachments'][] = [
            'filename' => $filename,
            'content' => $base64Content,
        ];

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
     * Send the composed email using the current payload.
     *
     * @return array The API response as an associative array.
     *
     * @throws \Exception On HTTP or API failure.
     */
    public function send(): array
    {
        $headers = [];

        if ($this->idempotencyKey !== null) {
            $headers['Idempotency-Key'] = $this->idempotencyKey;
        }

        return $this->httpClient->post('/v1/send', $this->payload, $headers);
    }
}
