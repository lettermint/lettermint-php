<?php

declare(strict_types=1);

namespace Lettermint\Exceptions;

/**
 * HTTP 401: the token is missing, invalid or revoked.
 */
class AuthenticationException extends ApiException {}
