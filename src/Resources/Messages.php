<?php

declare(strict_types=1);

namespace Lettermint\Resources;

use Generator;
use Lettermint\Types\ListMessageEventsResponse;
use Lettermint\Types\ListMessagesResponse;
use Lettermint\Types\MessageData;
use Lettermint\Types\MessageEventData;
use Lettermint\Types\MessageListData;
use Lettermint\Types\ProcessInboundMessageResponse;
use Lettermint\Types\ScheduledMessage;

/**
 * Sent and received messages. Needs the team token; reschedule() and cancel()
 * also accept the sending token when no team token is configured.
 *
 * @phpstan-import-type ListMessagesQuery from \Lettermint\Types\ApiTypes
 * @phpstan-import-type ListMessageEventsQuery from \Lettermint\Types\ApiTypes
 * @phpstan-import-type RescheduleMessageRequest from \Lettermint\Types\ApiTypes
 */
final class Messages extends Resource
{
    /**
     * Lists messages, one page at a time.
     *
     * @param  ListMessagesQuery  $query
     */
    public function list(array $query = []): ListMessagesResponse
    {
        return $this->transport->object(ListMessagesResponse::class, 'GET /messages', 'messages.list', query: $query);
    }

    /**
     * Iterates over every message, following `next_cursor`.
     *
     * @param  ListMessagesQuery  $query
     * @return Generator<int, MessageListData, mixed, void>
     */
    public function iterate(array $query = []): Generator
    {
        return $this->transport->paginate(ListMessagesResponse::class, 'GET /messages', 'messages.iterate', query: $query);
    }

    public function retrieve(string $messageId): MessageData
    {
        return $this->transport->object(MessageData::class, 'GET /messages/{messageId}', 'messages.retrieve', ['messageId' => $messageId]);
    }

    /**
     * Lists the events of a message, one page at a time.
     *
     * @param  ListMessageEventsQuery  $query
     */
    public function events(string $messageId, array $query = []): ListMessageEventsResponse
    {
        return $this->transport->object(ListMessageEventsResponse::class, 'GET /messages/{messageId}/events', 'messages.events', ['messageId' => $messageId], $query);
    }

    /**
     * Iterates over every event of a message, following `next_cursor`.
     *
     * @param  ListMessageEventsQuery  $query
     * @return Generator<int, MessageEventData, mixed, void>
     */
    public function iterateEvents(string $messageId, array $query = []): Generator
    {
        return $this->transport->paginate(ListMessageEventsResponse::class, 'GET /messages/{messageId}/events', 'messages.iterateEvents', ['messageId' => $messageId], $query);
    }

    /**
     * The raw RFC 822 source.
     */
    public function source(string $messageId): string
    {
        return $this->transport->text('GET /messages/{messageId}/source', 'messages.source', ['messageId' => $messageId]);
    }

    /**
     * The HTML body.
     */
    public function html(string $messageId): string
    {
        return $this->transport->text('GET /messages/{messageId}/html', 'messages.html', ['messageId' => $messageId]);
    }

    /**
     * The plain-text body.
     */
    public function text(string $messageId): string
    {
        return $this->transport->text('GET /messages/{messageId}/text', 'messages.text', ['messageId' => $messageId]);
    }

    /**
     * Moves a scheduled message to another delivery time.
     *
     * @param  RescheduleMessageRequest  $body
     */
    public function reschedule(string $messageId, array $body): ScheduledMessage
    {
        return $this->transport->object(ScheduledMessage::class, 'PATCH /messages/{messageId}', 'messages.reschedule', ['messageId' => $messageId], json: $body);
    }

    /**
     * Cancels a scheduled message.
     */
    public function cancel(string $messageId): ScheduledMessage
    {
        return $this->transport->object(ScheduledMessage::class, 'POST /messages/{messageId}/cancel', 'messages.cancel', ['messageId' => $messageId]);
    }

    /**
     * Releases one quarantined inbound message for webhook delivery.
     *
     * @param  string|null  $idempotencyKey  Sent as the `Idempotency-Key` header of this call only.
     */
    public function process(string $messageId, ?string $idempotencyKey = null): ProcessInboundMessageResponse
    {
        return $this->transport->object(ProcessInboundMessageResponse::class, 'POST /messages/{messageId}/process', 'messages.process', ['messageId' => $messageId], idempotencyKey: $idempotencyKey);
    }
}
