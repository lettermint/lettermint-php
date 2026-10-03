<?php

declare(strict_types=1);

namespace Lettermint;

use ArrayAccess;
use JsonSerializable;
use LogicException;

/**
 * Base class of the generated API response classes in `Lettermint\Types`.
 *
 * An API object keeps the decoded JSON exactly as the API sent it, so fields
 * and enum values that this SDK version does not know yet pass through. Read
 * fields as read-only properties (`$domain->status`) or array offsets
 * (`$domain['status']`); nested objects are hydrated into their classes.
 * Optional fields that are absent read as `null`; use has() to tell an absent
 * field from a `null` one.
 *
 * @implements ArrayAccess<string, mixed>
 */
abstract class ApiObject implements ArrayAccess, JsonSerializable
{
    /**
     * Which fields hold nested API objects: a class name, `['list', spec]` or
     * `['map', spec]`. Generated.
     *
     * @var array<string, mixed>
     */
    protected const CASTS = [];

    /** @var array<array-key, mixed> */
    private array $attributes;

    /** @var array<array-key, mixed> */
    private array $values;

    /**
     * @param  array<array-key, mixed>  $attributes  The decoded JSON object.
     */
    final public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
        $values = $attributes;
        foreach (static::CASTS as $field => $spec) {
            if (array_key_exists($field, $values)) {
                $values[$field] = self::hydrate($spec, $values[$field]);
            }
        }
        $this->values = $values;
    }

    /**
     * @param  array<array-key, mixed>  $attributes  The decoded JSON object.
     */
    public static function from(array $attributes): static
    {
        return new static($attributes);
    }

    /**
     * Whether the API sent the field, even if its value is `null`.
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * A field by name, also one this SDK version does not know.
     */
    public function get(string $name, mixed $default = null): mixed
    {
        return array_key_exists($name, $this->values) ? $this->values[$name] : $default;
    }

    /**
     * The object as the API sent it (decoded JSON, without hydrated objects).
     *
     * @return array<array-key, mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    public function jsonSerialize(): mixed
    {
        return $this->attributes === [] ? new \stdClass : $this->attributes;
    }

    public function __get(string $name): mixed
    {
        return $this->values[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        return isset($this->values[$name]);
    }

    public function __set(string $name, mixed $value): never
    {
        throw new LogicException(static::class.' is read-only.');
    }

    public function __unset(string $name): never
    {
        throw new LogicException(static::class.' is read-only.');
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->values[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->values[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new LogicException(static::class.' is read-only.');
    }

    public function offsetUnset(mixed $offset): never
    {
        throw new LogicException(static::class.' is read-only.');
    }

    /**
     * @return array<array-key, mixed>
     */
    public function __debugInfo(): array
    {
        return $this->attributes;
    }

    /**
     * Hydrates a value by its cast spec. Values of an unexpected shape stay as
     * they are, so that a changed API response never fails decoding.
     */
    private static function hydrate(mixed $spec, mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }
        if (is_string($spec)) {
            if (! is_a($spec, self::class, true) || ($value !== [] && array_is_list($value))) {
                return $value;
            }

            return new $spec($value);
        }
        if (is_array($spec) && count($spec) === 2) {
            [$kind, $inner] = $spec;
            if ($kind === 'list' && array_is_list($value)) {
                return array_map(static fn (mixed $item): mixed => self::hydrate($inner, $item), $value);
            }
            if ($kind === 'map') {
                return array_map(static fn (mixed $item): mixed => self::hydrate($inner, $item), $value);
            }
        }

        return $value;
    }
}
