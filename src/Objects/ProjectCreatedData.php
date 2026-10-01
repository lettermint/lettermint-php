<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property \Lettermint\Objects\ProjectData $data
 * @property string $message
 * @property string $api_token
 */
final class ProjectCreatedData extends Resource
{
    protected static array $casts = [
        'data' => \Lettermint\Objects\ProjectData::class,
    ];
}
