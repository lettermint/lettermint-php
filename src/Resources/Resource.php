<?php

declare(strict_types=1);

namespace Lettermint\Resources;

use Lettermint\Exceptions\LettermintConfigException;
use Lettermint\Internal\Transport;

/**
 * Base of the sub-clients. Debug output of a sub-client shows no credentials,
 * and sub-clients cannot be serialized.
 */
abstract class Resource
{
    /**
     * @internal Use the properties of Lettermint\Lettermint.
     */
    public function __construct(protected readonly Transport $transport) {}

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return [];
    }

    /**
     * @return never
     */
    public function __serialize(): array
    {
        throw new LettermintConfigException(static::class.' cannot be serialized, because its client holds API tokens.');
    }
}
