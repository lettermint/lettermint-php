<?php

declare(strict_types=1);

namespace Lettermint\Exceptions;

/**
 * HTTP 422: the API rejected the request data.
 */
class ValidationException extends ApiException
{
    /**
     * @param  array<string, list<string>>|null  $errors  Field errors from the `{"message", "errors"}` body, when the API sent them.
     */
    public function __construct(
        int $status,
        string $message,
        ?string $errorCode = null,
        mixed $details = null,
        mixed $body = null,
        public readonly ?array $errors = null,
    ) {
        parent::__construct($status, $message, $errorCode, $details, $body);
    }
}
