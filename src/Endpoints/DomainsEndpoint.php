<?php

namespace Lettermint\Endpoints;

use Lettermint\Responses\DeleteDomainResponse;
use Lettermint\Responses\DomainListResponse;
use Lettermint\Responses\DomainResponse;
use Lettermint\Responses\UpdateDomainProjectsResponse;
use Lettermint\Responses\VerifyDnsRecordResponse;
use Lettermint\Responses\VerifyDnsRecordsResponse;

/**
 * @phpstan-import-type StoreDomainData from \Lettermint\Types\ApiTypes
 * @phpstan-import-type UpdateDomainProjectsData from \Lettermint\Types\ApiTypes
 */
class DomainsEndpoint extends Endpoint
{
    public function list(array $query = []): DomainListResponse
    {
        return $this->hydrate(DomainListResponse::class, $this->getArray($this->path('/domains'), $query));
    }

    /**
     * @phpstan-param StoreDomainData $data
     */
    public function create(array $data): DomainResponse
    {
        return $this->hydrate(DomainResponse::class, $this->postArray($this->path('/domains'), $data, []));
    }

    public function retrieve(string $domainId, array $query = []): DomainResponse
    {
        return $this->hydrate(DomainResponse::class, $this->getArray($this->path('/domains/{domainId}', ['domainId' => $domainId]), $query));
    }

    public function delete(string $domainId): DeleteDomainResponse
    {
        return $this->hydrate(DeleteDomainResponse::class, $this->deleteArray($this->path('/domains/{domainId}', ['domainId' => $domainId]), []));
    }

    public function verifyDnsRecords(string $domainId): VerifyDnsRecordsResponse
    {
        return $this->hydrate(VerifyDnsRecordsResponse::class, $this->postArray($this->path('/domains/{domainId}/dns-records/verify', ['domainId' => $domainId]), [], []));
    }

    public function verifyDnsRecord(string $domainId, string $recordId): VerifyDnsRecordResponse
    {
        return $this->hydrate(VerifyDnsRecordResponse::class, $this->postArray($this->path('/domains/{domainId}/dns-records/{recordId}/verify', [
            'domainId' => $domainId,
            'recordId' => $recordId,
        ]), [], []));
    }

    /**
     * @phpstan-param UpdateDomainProjectsData $data
     */
    public function updateProjects(string $domainId, array $data): UpdateDomainProjectsResponse
    {
        return $this->hydrate(UpdateDomainProjectsResponse::class, $this->putArray($this->path('/domains/{domainId}/projects', ['domainId' => $domainId]), $data, []));
    }
}
