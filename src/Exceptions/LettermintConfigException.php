<?php

declare(strict_types=1);

namespace Lettermint\Exceptions;

/**
 * The client was configured or called incorrectly: a missing or unrecognised
 * token, a token that the called method cannot use, an invalid option or an
 * invalid path parameter. Thrown before any request is made.
 */
class LettermintConfigException extends LettermintException {}
