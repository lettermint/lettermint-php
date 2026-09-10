<?php

namespace Lettermint\Objects;

use InvalidArgumentException;

/** A reusable exact-match message tag. */
final readonly class MessageTag
{
    public function __construct(
        public string $name,
        public string $value,
    ) {
        if (! preg_match('/^[A-Za-z0-9_-]{1,32}$/D', $name)) {
            throw new InvalidArgumentException('Message tag names must match ^[A-Za-z0-9_-]{1,32}$');
        }

        if (str_starts_with(strtolower($name), '__lettermint')) {
            throw new InvalidArgumentException('Message tag names must not start with __lettermint');
        }

        if (! preg_match('/^[A-Za-z0-9_-]{1,64}$/D', $value)) {
            throw new InvalidArgumentException('Message tag values must match ^[A-Za-z0-9_-]{1,64}$');
        }
    }

    /** @return array{name: string, value: string} */
    public function toArray(): array
    {
        return ['name' => $this->name, 'value' => $this->value];
    }
}
