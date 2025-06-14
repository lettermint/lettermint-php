<?php

namespace Lettermint\Endpoints;

class EmailEndpoint extends Endpoint
{
    /**
     * @var array The email payload to be sent.
     */
    private array $payload = [];

    /**
     * Set the custom headeres.
     *
     * @example ["Key" => "Value"]
     *
     * @param array<string, string> $headers The custom headers.
     * @return self
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
     * @param string $email The sender's email address.
     * @return self
     */
    public function from(string $email): self
    {
        $this->payload['from'] = $email;
        return $this;
    }

    /**
     * Set one or more recipient email addresses.
     *
     * @param string ...$emails One or more recipient email addresses.
     * @return self
     */
    public function to(string ...$emails): self
    {
        $this->payload['to'] = $emails;
        return $this;
    }

    /**
     * Set the subject of the email.
     *
     * @param string $subject The subject line.
     * @return self
     */
    public function subject(string $subject): self
    {
        $this->payload['subject'] = $subject;
        return $this;
    }

    /**
     * Set the HTML body of the email.
     *
     * @param string|null $html The HTML content for the email body.
     * @return self
     */
    public function html(?string $html): self
    {
        $this->payload['html'] = $html;
        return $this;
    }

    /**
     * Set the plain text body of the email.
     *
     * @param string|null $text The plain text content for the email body.
     * @return self
     */
    public function text(?string $text): self
    {
        $this->payload['text'] = $text;
        return $this;
    }

    /**
     * Set one or more CC email addresses.
     *
     * @param string ...$emails Email addresses to be CC'd.
     * @return self
     */
    public function cc(string ...$emails): self
    {
        $this->payload['cc'] = $emails;
        return $this;
    }

    /**
     * Set one or more BCC email addresses.
     *
     * @param string ...$emails Email addresses to be BCC'd.
     * @return self
     */
    public function bcc(string ...$emails): self
    {
        $this->payload['bcc'] = $emails;
        return $this;
    }

    /**
     * Set one or more Reply-To email addresses.
     *
     * @param string ...$emails Reply-To email addresses.
     * @return self
     */
    public function replyTo(string ...$emails): self
    {
        $this->payload['reply_to'] = $emails;
        return $this;
    }

    /**
     * Attach a file to the email.
     *
     * @param string $filename The attachment filename.
     * @param string $base64Content The base64-encoded file content.
     * @return self
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
     * @param string $route The route id to use for sending.
     * @return self
     */
    public function route(string $route): self
    {
        $this->payload['route'] = $route;
        return $this;
    }

    /**
     * Send the composed email using the current payload.
     *
     * @return array The API response as an associative array.
     * @throws \Exception On HTTP or API failure.
     */
    public function send(): array
    {
        return $this->httpClient->post('/v1/send', $this->payload);
    }
}
