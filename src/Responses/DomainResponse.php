<?php

namespace Lettermint\Responses;

use Lettermint\Resource;

/**
 * @property string $id
 * @property string $domain
 * @property string|null $status_changed_at
 * @property list<\Lettermint\Objects\DomainDnsRecordData> $dns_records
 * @property list<array<string, mixed>> $projects
 * @property string $created_at
 */
final class DomainResponse extends Resource
{
    //
}
