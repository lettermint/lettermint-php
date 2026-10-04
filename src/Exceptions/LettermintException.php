<?php

declare(strict_types=1);

namespace Lettermint\Exceptions;

use RuntimeException;

/**
 * Base class of every exception the Lettermint SDK throws.
 *
 * No SDK exception carries request headers or API tokens.
 */
class LettermintException extends RuntimeException {}
