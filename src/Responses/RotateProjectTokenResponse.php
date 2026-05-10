<?php

namespace Lettermint\Responses;

use Lettermint\Resource;

/**
 * @property \Lettermint\Objects\ProjectData $data
 * @property string $new_token
 * @property string $message
 */
final class RotateProjectTokenResponse extends Resource
{
    protected static array $casts = [
        'data' => \Lettermint\Objects\ProjectData::class,
    ];
}
