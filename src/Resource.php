<?php

namespace Lettermint;

use ArrayAccess;
use BadMethodCallException;
use JsonSerializable;

/**
 * @implements ArrayAccess<string, mixed>
 */
class Resource implements ArrayAccess, JsonSerializable
{
    /**
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /** @var array<string, mixed> */
    protected static array $casts = [];

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function __get(string $name): mixed
    {
        return $this->getAttribute($name);
    }

    public function getAttribute(string $name): mixed
    {
        if (! array_key_exists($name, $this->attributes)) {
            return null;
        }

        return $this->castAttribute($name, $this->attributes[$name]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->arrayify($this->attributes);
    }

    /**
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return $this->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return $this->toArray();
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists((string) $offset, $this->attributes);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->getAttribute((string) $offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new BadMethodCallException('Cannot set resource attributes.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException('Cannot unset resource attributes.');
    }

    private function castAttribute(string $name, mixed $value): mixed
    {
        $cast = static::$casts[$name] ?? null;

        if ($cast === null || $value === null) {
            return $value;
        }

        if (is_string($cast) && is_a($cast, self::class, true) && is_array($value)) {
            return new $cast($value);
        }

        if (is_array($cast) && is_string($cast[0] ?? null) && is_a($cast[0], self::class, true) && is_array($value)) {
            return array_map(
                fn (mixed $item): mixed => is_array($item) ? new $cast[0]($item) : $item,
                $value
            );
        }

        return $value;
    }

    /**
     * @param  array<array-key, mixed>  $values
     * @return array<array-key, mixed>
     */
    private function arrayify(array $values): array
    {
        return array_map(function (mixed $value): mixed {
            if ($value instanceof self) {
                return $value->toArray();
            }

            if (is_array($value)) {
                return $this->arrayify($value);
            }

            return $value;
        }, $values);
    }
}
