<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string|null $email
 * @property list<string>|null $emails
 * @property 'spam_complaint'|'hard_bounce'|'unsubscribe'|'manual' $reason
 * @property 'team'|'project'|'route' $scope
 * @property string|null $route_id
 * @property string|null $project_id
 * @property 'all'|'broadcast'|null $applies_to
 */
final class StoreSuppressionData extends Resource
{
    //
}
