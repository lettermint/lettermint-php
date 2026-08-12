<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $id
 * @property string $name
 * @property 'owner'|'admin'|'member'|null $system_key
 * @property list<string> $permissions
 * @property bool $assignable
 */
final class TeamRoleData extends Resource
{
    //
}
