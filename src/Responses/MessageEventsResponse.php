<?php

namespace Lettermint\Responses;

use Lettermint\Resource;

/**
 * @property list<\Lettermint\Objects\MessageEventData> $data
 * @property list<string> $links
 * @property array<string, mixed> $meta
 */
final class MessageEventsResponse extends Resource
{
    protected static array $casts = [
        'data' => [\Lettermint\Objects\MessageEventData::class],
    ];
}
