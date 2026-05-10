<?php

namespace Lettermint\Responses;

use Lettermint\Resource;

/**
 * @property list<\Lettermint\Responses\SendMailResponse> $data
 */
final class SendBatchMailResponse extends Resource
{
    protected static array $casts = [
        'data' => [\Lettermint\Responses\SendMailResponse::class],
    ];
}
