<?php

namespace Lettermint\Responses;

use Lettermint\Resource;

/**
 * @property \Lettermint\Objects\DomainData $data
 * @property string $message
 */
final class UpdateDomainProjectsResponse extends Resource
{
    protected static array $casts = [
        'data' => \Lettermint\Objects\DomainData::class,
    ];
}
