<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property string $id
 * @property 'TXT'|'CNAME'|'MX' $type
 * @property string $hostname
 * @property string $fqdn
 * @property string $content
 * @property 'active'|'failed'|'pending' $status
 * @property string|null $verified_at
 * @property string|null $last_checked_at
 */
final class DomainDnsRecordData extends Resource
{
    //
}
