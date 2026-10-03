<?php

declare(strict_types=1);

namespace Lettermint\Exceptions;

/**
 * The request did not complete within the timeout. The timeout covers the
 * whole request, including reading the response body. The API may still have
 * processed the request: retry a send with the same idempotency key.
 */
class TimeoutException extends LettermintException
{
    /**
     * @param  float  $timeout  The timeout in seconds.
     */
    public function __construct(public readonly float $timeout)
    {
        parent::__construct(sprintf('The request to the Lettermint API timed out after %s seconds.', self::seconds($timeout)));
    }

    private static function seconds(float $timeout): string
    {
        return rtrim(rtrim(number_format($timeout, 3, '.', ''), '0'), '.');
    }
}
