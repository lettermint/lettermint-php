<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string|null $email
 * @property 'spam_complaint'|'hard_bounce'|'unsubscribe'|'manual' $reason
 * @property 'team'|'project'|'route' $scope
 * @property string|null $route_id
 * @property string|null $project_id
 * @property list<string>|null $emails
 */
final class StoreSuppressionData extends Resource
{
    //
}
