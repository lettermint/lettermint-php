<?php

namespace Lettermint\Endpoints;

/**
 * @phpstan-import-type CursorPage from \Lettermint\Types\ApiTypes
 * @phpstan-import-type ApiObject from \Lettermint\Types\ApiTypes
 * @phpstan-import-type StoreDomainData from \Lettermint\Types\ApiTypes
 * @phpstan-import-type UpdateDomainProjectsData from \Lettermint\Types\ApiTypes
 */
class DomainsEndpoint extends Endpoint
{
    /** @phpstan-return CursorPage */
    public function list(array $query = []): array
    {
        return $this->getArray($this->path('/domains'), $query);
    }

    /**
     * @phpstan-param StoreDomainData $data
     *
     * @phpstan-return ApiObject
     */
    public function create(array $data): array
    {
        return $this->postArray($this->path('/domains'), $data, []);
    }

    /** @phpstan-return ApiObject */
    public function retrieve(string $domainId, array $query = []): array
    {
        return $this->getArray($this->path('/domains/{domainId}', ['domainId' => $domainId]), $query);
    }

    /** @phpstan-return ApiObject */
    public function delete(string $domainId): array
    {
        return $this->deleteArray($this->path('/domains/{domainId}', ['domainId' => $domainId]), []);
    }

    /** @phpstan-return ApiObject */
    public function verifyDnsRecords(string $domainId): array
    {
        return $this->postArray($this->path('/domains/{domainId}/dns-records/verify', ['domainId' => $domainId]), [], []);
    }

    /** @phpstan-return ApiObject */
    public function verifyDnsRecord(string $domainId, string $recordId): array
    {
        return $this->postArray($this->path('/domains/{domainId}/dns-records/{recordId}/verify', [
            'domainId' => $domainId,
            'recordId' => $recordId,
        ]), [], []);
    }

    /**
     * @phpstan-param UpdateDomainProjectsData $data
     *
     * @phpstan-return ApiObject
     */
    public function updateProjects(string $domainId, array $data): array
    {
        return $this->putArray($this->path('/domains/{domainId}/projects', ['domainId' => $domainId]), $data, []);
    }
}
