<?php

namespace Lettermint\Responses;

use Lettermint\Resource;

/**
 * @property \Lettermint\Objects\TeamData $data
 * @property string $message
 */
final class UpdateTeamResponse extends Resource
{
    protected static array $casts = [
        'data' => \Lettermint\Objects\TeamData::class,
    ];
}
