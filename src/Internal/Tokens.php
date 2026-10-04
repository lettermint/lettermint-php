<?php

declare(strict_types=1);

namespace Lettermint\Internal;

use Lettermint\Exceptions\LettermintConfigException;

/**
 * Token checks. Error messages never contain the token.
 *
 * @internal
 */
final class Tokens
{
    /** Team API tokens: `ApiToken::TEAM_PREFIX` in the Lettermint backend. */
    private const TEAM = '/\Alm_team_[0-9A-Za-z]+\z/';

    /** Project sending tokens (32 or 22 random characters): `ApiToken::PROJECT_PREFIX`. */
    private const SENDING = '/\Alm_[0-9A-Za-z]+\z/';

    /** Characters allowed in a token, so that it is a valid HTTP header value. */
    private const HEADER_SAFE = '/\A[\x21-\x7e]+\z/';

    /**
     * Classifies a token passed as `new Lettermint($token)`. The team pattern is
     * checked first, because every team token also starts with `lm_`.
     *
     * @return 'sending'|'team'
     */
    public static function detect(#[\SensitiveParameter] string $token): string
    {
        if (preg_match(self::TEAM, $token) === 1) {
            return 'team';
        }
        if (preg_match(self::SENDING, $token) === 1) {
            return 'sending';
        }

        throw new LettermintConfigException('Unrecognised token format; pass sendingToken or teamToken instead.');
    }

    /**
     * Validates an explicitly configured token. Returns `null` when it is not set.
     */
    public static function check(string $option, #[\SensitiveParameter] ?string $token): ?string
    {
        if ($token === null) {
            return null;
        }
        if ($token === '') {
            throw new LettermintConfigException("{$option} must be a non-empty string.");
        }
        if (preg_match(self::HEADER_SAFE, $token) !== 1) {
            throw new LettermintConfigException("{$option} contains whitespace or characters that are not allowed in an HTTP header.");
        }

        return $token;
    }
}
