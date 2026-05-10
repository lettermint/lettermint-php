<?php

namespace Lettermint\Responses;

use Lettermint\Resource;

/**
 * @property \Lettermint\Objects\ProjectData $data
 * @property string $message
 */
final class UpdateProjectResponse extends Resource
{
    protected static array $casts = [
        'data' => \Lettermint\Objects\ProjectData::class,
    ];
}
