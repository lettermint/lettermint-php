<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $id
 * @property 'email'|'domain'|'extension' $type
 * @property string $value
 * @property 'spam_complaint'|'hard_bounce'|'unsubscribe'|'manual' $reason
 * @property 'team'|'project'|'route' $scope
 * @property 'all'|'broadcast' $applies_to
 * @property string|null $project_id
 * @property string|null $route_id
 * @property \Lettermint\Objects\SuppressionSourceMessageData|null $source_message
 * @property string $created_at
 */
final class SuppressedRecipientData extends Resource
{
    //
}
