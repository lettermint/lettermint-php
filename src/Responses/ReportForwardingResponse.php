<?php

namespace Lettermint\Responses;

use Lettermint\Resource;

/**
 * @property \Lettermint\Objects\ReportForwardingResource $data
 */
final class ReportForwardingResponse extends Resource
{
    protected static array $casts = [
        'data' => \Lettermint\Objects\ReportForwardingResource::class,
    ];
}
