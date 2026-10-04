<?php

declare(strict_types=1);

namespace Lettermint\Exceptions;

/**
 * HTTP 403: the token may not perform this action, or the plan lacks the feature.
 */
class PermissionException extends ApiException {}
