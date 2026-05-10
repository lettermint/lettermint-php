<?php

namespace Lettermint\Responses;

use Lettermint\Resource;

/**
 * @property list<\Lettermint\Objects\SuppressedRecipientData> $data
 * @property string|null $path
 * @property int $per_page
 * @property string|null $next_cursor
 * @property string|null $next_page_url
 * @property string|null $prev_cursor
 * @property string|null $prev_page_url
 */
final class SuppressionListResponse extends Resource
{
    protected static array $casts = [
        'data' => [\Lettermint\Objects\SuppressedRecipientData::class],
    ];
}
