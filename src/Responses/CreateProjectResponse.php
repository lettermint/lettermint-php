<?php

namespace Lettermint\Responses;

use Lettermint\Resource;

/**
 * @property \Lettermint\Objects\ProjectData $data
 * @property string $message
 * @property string $api_token
 */
final class CreateProjectResponse extends Resource
{
    protected static array $casts = [
        'data' => \Lettermint\Objects\ProjectData::class,
    ];
}
