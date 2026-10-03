<?php

declare(strict_types=1);

namespace Lettermint;

use ArrayAccess;
use JsonSerializable;
use LogicException;

/**
 * A verified webhook delivery, returned by Webhook::verify().
 *
 * `event` is the event name, for example `message.delivered` (see
 * Types\WebhookEvent for the known ones; unknown events pass through). The
 * whole payload, including fields this SDK does not know, is available as an
 * array through toArray() and array offsets (`$payload['data']`).
 *
 * @implements ArrayAccess<string, mixed>
 */
final class WebhookPayload implements ArrayAccess, JsonSerializable
{
    /** The delivery id, when the payload has one. */
    public readonly ?string $id;

    /** The event name, for example `message.delivered`. */
    public readonly string $event;

    /** When the event occurred (ISO 8601), when the payload has it. */
    public readonly ?string $timestamp;

    /** @var array<array-key, mixed> The event data. */
    public readonly array $data;

    /**
     * @param  array<string, mixed>  $payload  The decoded payload; it must have a string `event`.
     */
    public function __construct(private readonly array $payload)
    {
        $event = $payload['event'] ?? null;
        if (! is_string($event)) {
            throw new \InvalidArgumentException('A webhook payload needs a string event.');
        }
        $this->event = $event;
        $this->id = is_string($payload['id'] ?? null) ? $payload['id'] : null;
        $this->timestamp = is_string($payload['timestamp'] ?? null) ? $payload['timestamp'] : null;
        $this->data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
    }

    /**
     * The whole payload as decoded from the request body.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->payload;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->payload[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->payload[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new LogicException('A webhook payload is read-only.');
    }

    public function offsetUnset(mixed $offset): never
    {
        throw new LogicException('A webhook payload is read-only.');
    }
}
