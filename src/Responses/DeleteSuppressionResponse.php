<?php

namespace Lettermint\Responses;

use Lettermint\Resource;

/**
 * @property bool $success
 * @property 'removed'|'review_ticket_created'|'review_ticket_exists' $status
 * @property string $message
 * @property float|int $confidence
 * @property string $ticket_identifier
 */
final class DeleteSuppressionResponse extends Resource
{
    //
}
