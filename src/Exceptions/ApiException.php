<?php

declare(strict_types=1);

namespace Lettermint\Exceptions;

/**
 * The API answered with an error status (4xx or 5xx) and a JSON or empty body.
 * Subclasses cover the common statuses. getCode() returns the HTTP status.
 */
class ApiException extends LettermintException
{
    /**
     * @param  int  $status  The HTTP status code.
     * @param  string  $message  The API's error message, or the HTTP reason phrase.
     * @param  string|null  $errorCode  Machine-readable error code from `{"error": {"code": ...}}` (or a string `error`), if the API sent one.
     * @param  mixed  $details  Additional context from `{"error": {"details": ...}}`, if the API sent any.
     * @param  mixed  $body  The decoded JSON error body, or `null` for an empty body.
     */
    public function __construct(
        public readonly int $status,
        string $message,
        public readonly ?string $errorCode = null,
        public readonly mixed $details = null,
        public readonly mixed $body = null,
    ) {
        parent::__construct($message, $status);
    }
}
