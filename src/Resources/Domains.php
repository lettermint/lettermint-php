<?php

declare(strict_types=1);

namespace Lettermint\Resources;

use Generator;
use Lettermint\Types\DnsVerificationSuccessResponse;
use Lettermint\Types\DomainData;
use Lettermint\Types\DomainListData;
use Lettermint\Types\DomainMutationResponse;
use Lettermint\Types\ListDomainsResponse;
use Lettermint\Types\MessageResponse;

/**
 * Sending domains. Needs the team token.
 *
 * @phpstan-import-type ListDomainsQuery from \Lettermint\Types\ApiTypes
 * @phpstan-import-type GetDomainQuery from \Lettermint\Types\ApiTypes
 * @phpstan-import-type StoreDomainData from \Lettermint\Types\ApiTypes
 * @phpstan-import-type UpdateDomainProjectsData from \Lettermint\Types\ApiTypes
 */
final class Domains extends Resource
{
    /**
     * Lists domains, one page at a time.
     *
     * @param  ListDomainsQuery  $query
     */
    public function list(array $query = []): ListDomainsResponse
    {
        return $this->transport->object(ListDomainsResponse::class, 'GET /domains', 'domains.list', query: $query);
    }

    /**
     * Iterates over every domain, following `next_cursor`.
     *
     * @param  ListDomainsQuery  $query
     * @return Generator<int, DomainListData, mixed, void>
     */
    public function iterate(array $query = []): Generator
    {
        return $this->transport->paginate(ListDomainsResponse::class, 'GET /domains', 'domains.iterate', query: $query);
    }

    /**
     * @param  StoreDomainData  $body
     */
    public function create(array $body): DomainData
    {
        return $this->transport->object(DomainData::class, 'POST /domains', 'domains.create', json: $body);
    }

    /**
     * @param  GetDomainQuery  $query  For example `['include' => ['dnsRecords']]`.
     */
    public function retrieve(string $domainId, array $query = []): DomainData
    {
        return $this->transport->object(DomainData::class, 'GET /domains/{domainId}', 'domains.retrieve', ['domainId' => $domainId], $query);
    }

    public function delete(string $domainId): MessageResponse
    {
        return $this->transport->object(MessageResponse::class, 'DELETE /domains/{domainId}', 'domains.delete', ['domainId' => $domainId]);
    }

    /**
     * Checks every DNS record of the domain.
     */
    public function verifyDnsRecords(string $domainId): DnsVerificationSuccessResponse
    {
        return $this->transport->object(DnsVerificationSuccessResponse::class, 'POST /domains/{domainId}/dns-records/verify', 'domains.verifyDnsRecords', ['domainId' => $domainId]);
    }

    /**
     * Checks one DNS record of the domain.
     */
    public function verifyDnsRecord(string $domainId, string $recordId): MessageResponse
    {
        return $this->transport->object(MessageResponse::class, 'POST /domains/{domainId}/dns-records/{recordId}/verify', 'domains.verifyDnsRecord', ['domainId' => $domainId, 'recordId' => $recordId]);
    }

    /**
     * Starts the upgrade of a `legacy_txt` domain to managed DKIM (HTTP 202).
     * The domain's `dns_records` then list two new DKIM CNAME records with
     * `verification_scope` `migration`; add them to your DNS. The current DKIM
     * TXT record keeps signing until the upgrade completes. When
     * `dkim_transition->state` is `ready_to_activate`, call
     * activateDkimUpgrade(). Calling this again returns the upgrade that is in
     * progress; a domain that already uses managed DKIM is returned unchanged
     * (HTTP 200). A domain that cannot be upgraded throws a
     * ValidationException (HTTP 422).
     */
    public function startDkimUpgrade(string $domainId): DomainMutationResponse
    {
        return $this->transport->object(DomainMutationResponse::class, 'POST /domains/{domainId}/dkim/upgrade', 'domains.startDkimUpgrade', ['domainId' => $domainId]);
    }

    /**
     * Cancels the managed DKIM upgrade and removes its DKIM CNAME records. The
     * domain keeps its DKIM TXT record. Without an upgrade in progress nothing
     * changes, and a key rotation is never cancelled. After the grace period
     * started, the upgrade cannot be cancelled (ValidationException, HTTP 422).
     */
    public function cancelDkimUpgrade(string $domainId): DomainMutationResponse
    {
        return $this->transport->object(DomainMutationResponse::class, 'DELETE /domains/{domainId}/dkim/upgrade', 'domains.cancelDkimUpgrade', ['domainId' => $domainId]);
    }

    /**
     * Activates the managed DKIM upgrade. Lettermint checks both DKIM CNAME
     * records again, then starts a grace period in which both keys are
     * published. The upgrade completes at `dkim_transition->completes_after`,
     * and `dkim_mode` then changes to `managed_cname`; after that you can
     * remove the old DKIM TXT record. Calling this again during the grace
     * period, or after the upgrade completed, changes nothing. Without an
     * upgrade, or before it is `ready_to_activate`, the API answers with a
     * ValidationException (HTTP 422).
     */
    public function activateDkimUpgrade(string $domainId): DomainMutationResponse
    {
        return $this->transport->object(DomainMutationResponse::class, 'POST /domains/{domainId}/dkim/upgrade/activate', 'domains.activateDkimUpgrade', ['domainId' => $domainId]);
    }

    /**
     * Starts a DKIM key rotation for a domain with managed DKIM (HTTP 202).
     * The DKIM CNAME records stay the same, so no DNS change is needed.
     * Lettermint publishes the new key, then keeps both keys published during
     * a grace period. Lettermint also rotates keys on a schedule, so you only
     * need this to replace a key early. Calling this again during a rotation
     * returns the rotation that is in progress. A domain that cannot be
     * rotated throws a ValidationException (HTTP 422).
     */
    public function rotateDkimKey(string $domainId): DomainMutationResponse
    {
        return $this->transport->object(DomainMutationResponse::class, 'POST /domains/{domainId}/dkim/rotate', 'domains.rotateDkimKey', ['domainId' => $domainId]);
    }

    /**
     * Replaces the projects that may send from the domain.
     *
     * @param  UpdateDomainProjectsData  $body
     */
    public function updateProjects(string $domainId, array $body): DomainMutationResponse
    {
        return $this->transport->object(DomainMutationResponse::class, 'PUT /domains/{domainId}/projects', 'domains.updateProjects', ['domainId' => $domainId], json: $body);
    }
}
