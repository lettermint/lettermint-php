<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $id
 * @property string $domain
 * @property 'verified'|'partially_verified'|'pending_verification'|'failed_verification' $status
 * @property string|null $status_changed_at
 * @property string $created_at
 */
final class DomainListData extends Resource
{
    //
}
