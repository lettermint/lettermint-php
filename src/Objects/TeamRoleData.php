<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $id
 * @property string $name
 * @property 'owner'|'admin'|'member'|null $system_key
 * @property list<'team:manage'|'billing:manage'|'security:manage'|'audit:read'|'support:manage'|'members:read'|'members:manage'|'roles:manage'|'team_tokens:read'|'team_tokens:manage'|'team_tokens:rotate'|'team_tokens:revoke'|'projects:create'|'team_suppressions:read'|'team_suppressions:add'|'team_suppressions:remove'|'projects:read'|'projects:manage'|'projects:delete'|'routes:read'|'routes:manage'|'routes:delete'|'domains:read'|'domains:manage'|'domains:delete'|'project_tokens:read'|'project_tokens:manage'|'project_tokens:rotate'|'project_tokens:revoke'|'webhooks:read'|'webhooks:manage'|'webhooks:delete'|'webhooks:rotate_secret'|'stats:read'|'messages:read'|'messages:read_content'|'messages:send'|'suppressions:read'|'suppressions:add'|'suppressions:remove'> $permissions
 * @property bool $assignable
 */
final class TeamRoleData extends Resource
{
    //
}
