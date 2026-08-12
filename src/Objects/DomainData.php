<?php

namespace Lettermint\Objects;

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
final class DomainData extends Resource
{
    protected static array $casts = [
        'dns_records' => [DomainDnsRecordData::class],
    ];
}
