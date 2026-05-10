<?php

namespace Lettermint\Endpoints;

use Lettermint\Responses\DeleteRouteResponse;
use Lettermint\Responses\RouteResponse;
use Lettermint\Responses\UpdateRouteResponse;
use Lettermint\Responses\VerifyInboundDomainResponse;

/**
 * @phpstan-import-type UpdateRouteData from \Lettermint\Types\ApiTypes
 */
class RoutesEndpoint extends Endpoint
{
    public function retrieve(string $routeId, array $query = []): RouteResponse
    {
        return $this->hydrate(RouteResponse::class, $this->getArray($this->path('/routes/{routeId}', ['routeId' => $routeId]), $query));
    }

    /**
     * @phpstan-param UpdateRouteData $data
     */
    public function update(string $routeId, array $data): UpdateRouteResponse
    {
        return $this->hydrate(UpdateRouteResponse::class, $this->putArray($this->path('/routes/{routeId}', ['routeId' => $routeId]), $data, []));
    }

    public function delete(string $routeId): DeleteRouteResponse
    {
        return $this->hydrate(DeleteRouteResponse::class, $this->deleteArray($this->path('/routes/{routeId}', ['routeId' => $routeId]), []));
    }

    public function verifyInboundDomain(string $routeId): VerifyInboundDomainResponse
    {
        return $this->hydrate(VerifyInboundDomainResponse::class, $this->postArray($this->path('/routes/{routeId}/verify-inbound-domain', ['routeId' => $routeId]), [], []));
    }
}
