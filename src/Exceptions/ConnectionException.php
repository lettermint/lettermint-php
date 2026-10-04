<?php

declare(strict_types=1);

namespace Lettermint\Exceptions;

/**
 * The request could not be sent or the connection failed (DNS, TLS, refused,
 * reset). The underlying HTTP client exception is not attached, because it
 * holds the request and with it the API token; its message is.
 */
class ConnectionException extends LettermintException {}
