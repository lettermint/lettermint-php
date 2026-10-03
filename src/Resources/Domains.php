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
     * Replaces the projects that may send from the domain.
     *
     * @param  UpdateDomainProjectsData  $body
     */
    public function updateProjects(string $domainId, array $body): DomainMutationResponse
    {
        return $this->transport->object(DomainMutationResponse::class, 'PUT /domains/{domainId}/projects', 'domains.updateProjects', ['domainId' => $domainId], json: $body);
    }
}
