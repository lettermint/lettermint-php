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
 * @property 'return_path'|'dmarc'|'dkim_legacy'|'dkim_primary'|'dkim_secondary' $purpose
 * @property 'required'|'recommended'|'migration'|'deprecated' $verification_scope
 * @property bool $required_for_verification
 * @property string|null $verified_at
 * @property string|null $last_checked_at
 */
final class DomainDnsRecordData extends Resource
{
    //
}
