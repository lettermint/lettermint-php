<?php

declare(strict_types=1);

namespace Lettermint\Exceptions;

/**
 * HTTP 409: the request conflicts with the current state, for example an Idempotency-Key reused with a different body.
 */
class ConflictException extends ApiException {}
