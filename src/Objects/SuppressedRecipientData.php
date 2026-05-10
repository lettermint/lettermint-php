<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $id
 * @property 'email'|'domain'|'extension' $type
 * @property string $value
 * @property 'spam_complaint'|'hard_bounce'|'unsubscribe'|'manual' $reason
 * @property 'global'|'team'|'project'|'route' $scope
 * @property string|null $project_id
 * @property string|null $route_id
 * @property string $created_at
 * @property string $updated_at
 */
final class SuppressedRecipientData extends Resource
{
    //
}
