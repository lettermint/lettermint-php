<?php

declare(strict_types=1);

namespace Lettermint\Exceptions;

/**
 * HTTP 5xx with a JSON or empty body.
 */
class ServerException extends ApiException
{
    /**
     * @param  int|null  $retryAfter  Seconds to wait, from the `Retry-After` header, when the API sent one.
     */
    public function __construct(
        int $status,
        string $message,
        ?string $errorCode = null,
        mixed $details = null,
        mixed $body = null,
        public readonly ?int $retryAfter = null,
    ) {
        parent::__construct($status, $message, $errorCode, $details, $body);
    }
}
