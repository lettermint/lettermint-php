<?php

declare(strict_types=1);

namespace Lettermint\Resources;

use Generator;
use Lettermint\Types\InboundDomainVerificationResponse;
use Lettermint\Types\ListRoutesResponse;
use Lettermint\Types\MessageResponse;
use Lettermint\Types\RouteData;
use Lettermint\Types\RouteListData;
use Lettermint\Types\RouteMutationResponse;

/**
 * Routes of a project. Needs the team token.
 *
 * @phpstan-import-type ListRoutesQuery from \Lettermint\Types\ApiTypes
 * @phpstan-import-type GetRouteQuery from \Lettermint\Types\ApiTypes
 * @phpstan-import-type StoreRouteData from \Lettermint\Types\ApiTypes
 * @phpstan-import-type UpdateRouteData from \Lettermint\Types\ApiTypes
 */
final class Routes extends Resource
{
    /**
     * Lists the routes of a project, one page at a time.
     *
     * @param  ListRoutesQuery  $query
     */
    public function list(string $projectId, array $query = []): ListRoutesResponse
    {
        return $this->transport->object(ListRoutesResponse::class, 'GET /projects/{projectId}/routes', 'routes.list', ['projectId' => $projectId], $query);
    }

    /**
     * Iterates over every route of a project, following `next_cursor`.
     *
     * @param  ListRoutesQuery  $query
     * @return Generator<int, RouteListData, mixed, void>
     */
    public function iterate(string $projectId, array $query = []): Generator
    {
        return $this->transport->paginate(ListRoutesResponse::class, 'GET /projects/{projectId}/routes', 'routes.iterate', ['projectId' => $projectId], $query);
    }

    /**
     * @param  StoreRouteData  $body
     */
    public function create(string $projectId, array $body): RouteMutationResponse
    {
        return $this->transport->object(RouteMutationResponse::class, 'POST /projects/{projectId}/routes', 'routes.create', ['projectId' => $projectId], json: $body);
    }

    /**
     * @param  GetRouteQuery  $query
     */
    public function retrieve(string $routeId, array $query = []): RouteData
    {
        return $this->transport->object(RouteData::class, 'GET /routes/{routeId}', 'routes.retrieve', ['routeId' => $routeId], $query);
    }

    /**
     * @param  UpdateRouteData  $body
     */
    public function update(string $routeId, array $body): RouteMutationResponse
    {
        return $this->transport->object(RouteMutationResponse::class, 'PUT /routes/{routeId}', 'routes.update', ['routeId' => $routeId], json: $body);
    }

    public function delete(string $routeId): MessageResponse
    {
        return $this->transport->object(MessageResponse::class, 'DELETE /routes/{routeId}', 'routes.delete', ['routeId' => $routeId]);
    }

    public function verifyInboundDomain(string $routeId): InboundDomainVerificationResponse
    {
        return $this->transport->object(InboundDomainVerificationResponse::class, 'POST /routes/{routeId}/verify-inbound-domain', 'routes.verifyInboundDomain', ['routeId' => $routeId]);
    }
}
