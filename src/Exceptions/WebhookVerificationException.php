<?php

declare(strict_types=1);

namespace Lettermint\Exceptions;

/**
 * A webhook delivery could not be verified. Reject the request and do not
 * process its payload. `reason` says why, as one of the constants below.
 */
class WebhookVerificationException extends LettermintException
{
    /** The `X-Lettermint-Signature` header is missing or empty. */
    public const SIGNATURE_HEADER_MISSING = 'signature_header_missing';

    /** The signature header is malformed, or the request has more than one. */
    public const SIGNATURE_HEADER_MALFORMED = 'signature_header_malformed';

    /** The `X-Lettermint-Delivery` header is missing. */
    public const DELIVERY_HEADER_MISSING = 'delivery_header_missing';

    /** The `X-Lettermint-Delivery` header does not equal the signed timestamp. */
    public const DELIVERY_TIMESTAMP_MISMATCH = 'delivery_timestamp_mismatch';

    /** The signed timestamp is further from now than the tolerance. */
    public const TIMESTAMP_OUT_OF_TOLERANCE = 'timestamp_out_of_tolerance';

    /** No `v1` signature matches the body. */
    public const SIGNATURE_MISMATCH = 'signature_mismatch';

    /** The raw body is empty. */
    public const BODY_INVALID = 'body_invalid';

    /** The body is not a JSON object with an `event`. */
    public const PAYLOAD_INVALID = 'payload_invalid';

    /**
     * @param  self::*  $reason
     */
    public function __construct(public readonly string $reason, string $message)
    {
        parent::__construct($message);
    }
}
