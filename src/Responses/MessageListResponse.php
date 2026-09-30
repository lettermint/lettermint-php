<?php

namespace Lettermint\Responses;

use Lettermint\Resource;

/**
 * @property list<\Lettermint\Objects\MessageListData> $data
 * @property list<string> $links
 * @property array<string, mixed> $meta
 */
final class MessageListResponse extends Resource
{
    protected static array $casts = [
        'data' => [\Lettermint\Objects\MessageListData::class],
    ];
}
