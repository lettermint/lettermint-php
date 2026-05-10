<?php

namespace Lettermint\Responses;

use Lettermint\Resource;

/**
 * @property \Lettermint\Objects\RouteData $data
 * @property string $message
 */
final class UpdateRouteResponse extends Resource
{
    protected static array $casts = [
        'data' => \Lettermint\Objects\RouteData::class,
    ];
}
