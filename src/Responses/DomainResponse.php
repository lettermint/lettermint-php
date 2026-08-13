<?php

namespace Lettermint\Responses;

use Lettermint\Objects\DomainDnsRecordData;
use Lettermint\Resource;

/**
 * @property string $id
 * @property string $domain
 * @property 'legacy_txt'|'managed_cname' $dkim_mode
 * @property bool $rotation_ready
 * @property string|null $status_changed_at
 * @property list<DomainDnsRecordData> $dns_records
 * @property list<array<string, mixed>> $projects
 * @property string $created_at
 */
final class DomainResponse extends Resource
{
    //
}
