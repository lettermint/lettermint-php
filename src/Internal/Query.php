<?php

declare(strict_types=1);

namespace Lettermint\Internal;

use BackedEnum;
use DateTimeInterface;
use DateTimeZone;
use Lettermint\Exceptions\LettermintValidationException;
use Stringable;

/**
 * Serializes nested query arrays to the API's bracket syntax, exactly like the
 * Node SDK: `['page' => ['size' => 10], 'filter' => ['status' => 'verified'],
 * 'sort' => ['-created_at']]` becomes
 * `page%5Bsize%5D=10&filter%5Bstatus%5D=verified&sort=-created_at`.
 * Lists of scalars are comma-separated, lists of arrays are indexed, booleans
 * are `1`/`0`, and `null` values are left out.
 *
 * @internal
 */
final class Query
{
    /**
     * @param  array<array-key, mixed>  $query
     */
    public static function serialize(array $query): string
    {
        $pairs = [];
        foreach ($query as $key => $value) {
            self::append($pairs, (string) $key, $value);
        }

        return implode('&', array_map(
            static fn (array $pair): string => self::encode($pair[0]).'='.self::encode($pair[1]),
            $pairs,
        ));
    }

    /**
     * Returns a copy of `$query` with the parameter at wire name `$name`
     * (`cursor` or `page[cursor]`) set to `$value`.
     *
     * @param  array<array-key, mixed>  $query
     * @return array<array-key, mixed>
     */
    public static function with(array $query, string $name, string $value): array
    {
        if (preg_match('/\A([^\[\]]+)\[([^\[\]]+)\]\z/', $name, $match) !== 1) {
            $query[$name] = $value;

            return $query;
        }
        [, $group, $key] = $match;
        unset($query[$name]);
        $current = $query[$group] ?? [];
        $current = is_array($current) && ($current === [] || ! array_is_list($current)) ? $current : [];
        $current[$key] = $value;
        $query[$group] = $current;

        return $query;
    }

    /**
     * @param  list<array{0: string, 1: string}>  $pairs
     */
    private static function append(array &$pairs, string $key, mixed $value): void
    {
        if ($value === null) {
            return;
        }
        if (is_array($value)) {
            if (array_is_list($value)) {
                if (array_filter($value, static fn (mixed $item): bool => ! self::isScalar($item)) === []) {
                    $items = array_map(self::scalar(...), array_values(array_filter($value, static fn (mixed $item): bool => $item !== null)));
                    if ($items !== []) {
                        $pairs[] = [$key, implode(',', $items)];
                    }

                    return;
                }
                foreach ($value as $index => $item) {
                    self::append($pairs, "{$key}[{$index}]", $item);
                }

                return;
            }
            foreach ($value as $name => $item) {
                self::append($pairs, "{$key}[{$name}]", $item);
            }

            return;
        }
        $pairs[] = [$key, self::scalar($value)];
    }

    private static function isScalar(mixed $value): bool
    {
        return ! is_array($value);
    }

    private static function scalar(mixed $value): string
    {
        return match (true) {
            is_bool($value) => $value ? '1' : '0',
            is_string($value) => $value,
            is_int($value), is_float($value) => (string) $value,
            $value instanceof DateTimeInterface => \DateTimeImmutable::createFromInterface($value)
                ->setTimezone(new DateTimeZone('UTC'))
                ->format('Y-m-d\TH:i:s.v\Z'),
            $value instanceof BackedEnum => (string) $value->value,
            $value instanceof Stringable => (string) $value,
            default => throw new LettermintValidationException(
                'Query parameters must be strings, numbers, booleans, dates, arrays or null.',
                'query',
            ),
        };
    }

    /** Percent-encodes like WHATWG URLSearchParams (application/x-www-form-urlencoded). */
    private static function encode(string $value): string
    {
        return str_replace('%2A', '*', urlencode($value));
    }
}
