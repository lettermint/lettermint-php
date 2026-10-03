<?php

declare(strict_types=1);

namespace Lettermint\Exceptions;

/**
 * The SDK rejected a request before sending it, for example because of
 * invalid message tags. Unlike {@see ValidationException}, the API never saw
 * this request.
 */
class LettermintValidationException extends LettermintException
{
    /**
     * @param  string|null  $field  The offending field, for example `tags` or `messages[2].tags`.
     */
    public function __construct(string $message, public readonly ?string $field = null)
    {
        parent::__construct($message);
    }
}
