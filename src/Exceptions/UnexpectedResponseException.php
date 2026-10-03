<?php

declare(strict_types=1);

namespace Lettermint\Exceptions;

/**
 * The response could not be decoded: an empty or non-JSON body where JSON was
 * expected, or an error status with a non-JSON body such as a proxy's HTML
 * page. getCode() returns the HTTP status.
 */
class UnexpectedResponseException extends LettermintException
{
    /** The first 200 characters of the response body. */
    public readonly string $bodyExcerpt;

    /**
     * @param  int  $status  The HTTP status code.
     */
    public function __construct(string $message, public readonly int $status, string $body)
    {
        parent::__construct($message, $status);
        $this->bodyExcerpt = self::excerpt($body);
    }

    private static function excerpt(string $body): string
    {
        if (strlen($body) <= 200) {
            return $body;
        }
        $cut = substr($body, 0, 200);
        // Drop a multi-byte character that the cut split in two.
        for ($i = 0; $i < 3 && preg_match('//u', $cut) !== 1 && preg_match('/[\x80-\xff]\z/', $cut) === 1; $i++) {
            $cut = substr($cut, 0, -1);
        }

        return $cut.'…';
    }
}
