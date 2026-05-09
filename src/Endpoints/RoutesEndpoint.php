<?php

namespace Lettermint\Endpoints;

/**
 * @phpstan-import-type ApiObject from \Lettermint\Types\ApiTypes
 * @phpstan-import-type UpdateRouteData from \Lettermint\Types\ApiTypes
 */
class RoutesEndpoint extends Endpoint
{
    /** @phpstan-return ApiObject */
    public function retrieve(string $routeId, array $query = []): array
    {
        return $this->getArray($this->path('/routes/{routeId}', ['routeId' => $routeId]), $query);
    }

    /**
     * @phpstan-param UpdateRouteData $data
     *
     * @phpstan-return ApiObject
     */
    public function update(string $routeId, array $data): array
    {
        return $this->putArray($this->path('/routes/{routeId}', ['routeId' => $routeId]), $data, []);
    }

    /** @phpstan-return ApiObject */
    public function delete(string $routeId): array
    {
        return $this->deleteArray($this->path('/routes/{routeId}', ['routeId' => $routeId]), []);
    }

    /** @phpstan-return ApiObject */
    public function verifyInboundDomain(string $routeId): array
    {
        return $this->postArray($this->path('/routes/{routeId}/verify-inbound-domain', ['routeId' => $routeId]), [], []);
    }
}
