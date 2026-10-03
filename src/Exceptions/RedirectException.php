<?php

declare(strict_types=1);

namespace Lettermint\Exceptions;

/**
 * The API answered with a redirect (3xx). The SDK never follows redirects, so
 * that tokens are never sent to another location. getCode() returns the HTTP
 * status.
 */
class RedirectException extends LettermintException
{
    /**
     * @param  int  $status  The HTTP status code.
     */
    public function __construct(public readonly int $status)
    {
        parent::__construct(sprintf(
            'The Lettermint API answered with a redirect (HTTP %d). Redirects are not followed; check the baseUrl option.',
            $status,
        ), $status);
    }
}
