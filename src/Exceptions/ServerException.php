<?php

declare(strict_types=1);

namespace Lettermint\Exceptions;

/**
 * HTTP 5xx with a JSON or empty body.
 */
class ServerException extends ApiException {}
