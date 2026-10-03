<?php

declare(strict_types=1);

namespace Lettermint\Resources;

use Lettermint\EmailBuilder;
use Lettermint\Exceptions\LettermintValidationException;
use Lettermint\Internal\MessageValidator;
use Lettermint\Types\SendMailResponse;

/**
 * Sends email with the project sending token (`x-lettermint-token`).
 *
 * Holds no message state: every call sends exactly what it is given, so one
 * instance is safe to share across requests, queue jobs and long-running
 * workers (Octane, Horizon, Messenger).
 *
 * @phpstan-import-type SendMailRequest from \Lettermint\Types\ApiTypes
 */
final class Emails extends Resource
{
    /**
     * Sends one email.
     *
     * @param  SendMailRequest  $message  The email in the API's format (`reply_to`, `scheduled_at`, ...).
     * @param  string|null  $idempotencyKey  Sent as the `Idempotency-Key` header of this call only. A retry with the same key does not send the email again.
     */
    public function send(array $message, ?string $idempotencyKey = null): SendMailResponse
    {
        $this->transport->assertAuth('emails.send', 'sending');
        MessageValidator::message($message);

        return $this->transport->object(SendMailResponse::class, 'POST /send', 'emails.send', json: $message, idempotencyKey: $idempotencyKey);
    }

    /**
     * Sends up to 500 emails in one request. Accepts messages and builders.
     *
     * @param  list<SendMailRequest|EmailBuilder>  $messages
     * @param  string|null  $idempotencyKey  Sent as the `Idempotency-Key` header of this call only.
     * @return list<SendMailResponse> One result per message, in order.
     */
    public function sendBatch(array $messages, ?string $idempotencyKey = null): array
    {
        $this->transport->assertAuth('emails.sendBatch', 'sending');
        if (! array_is_list($messages)) {
            throw new LettermintValidationException('sendBatch() takes a list of messages.', 'messages');
        }
        $body = [];
        foreach ($messages as $index => $message) {
            if ($message instanceof EmailBuilder) {
                $body[] = $message->build();

                continue;
            }
            MessageValidator::message($message, "messages[{$index}]");
            $body[] = $message;
        }

        return $this->transport->objects(SendMailResponse::class, 'POST /send/batch', 'emails.sendBatch', json: $body, idempotencyKey: $idempotencyKey);
    }

    /**
     * Starts an immutable email builder. Every setter returns a new builder.
     * Pass a message to start from it.
     *
     * @param  SendMailRequest|array{}  $message
     */
    public function compose(array $message = []): EmailBuilder
    {
        $this->transport->assertAuth('emails.compose', 'sending');

        return EmailBuilder::start($this, $message);
    }

    /**
     * Checks the sending token: `GET /ping` returns `pong`.
     */
    public function ping(): string
    {
        return trim($this->transport->text('GET /ping', 'emails.ping', auth: 'sending'));
    }
}
