<?php

namespace Lettermint\Endpoints;

/**
 * @phpstan-import-type CursorPage from \Lettermint\Types\ApiTypes
 * @phpstan-import-type ApiObject from \Lettermint\Types\ApiTypes
 * @phpstan-import-type StoreProjectData from \Lettermint\Types\ApiTypes
 * @phpstan-import-type UpdateProjectData from \Lettermint\Types\ApiTypes
 * @phpstan-import-type UpdateProjectMembersData from \Lettermint\Types\ApiTypes
 * @phpstan-import-type StoreRouteData from \Lettermint\Types\ApiTypes
 */
class ProjectsEndpoint extends Endpoint
{
    /** @phpstan-return CursorPage */
    public function list(array $query = []): array
    {
        return $this->getArray($this->path('/projects'), $query);
    }

    /**
     * @phpstan-param StoreProjectData $data
     *
     * @phpstan-return ApiObject
     */
    public function create(array $data): array
    {
        return $this->postArray($this->path('/projects'), $data, []);
    }

    /** @phpstan-return ApiObject */
    public function retrieve(string $projectId, array $query = []): array
    {
        return $this->getArray($this->path('/projects/{projectId}', ['projectId' => $projectId]), $query);
    }

    /**
     * @phpstan-param UpdateProjectData $data
     *
     * @phpstan-return ApiObject
     */
    public function update(string $projectId, array $data): array
    {
        return $this->putArray($this->path('/projects/{projectId}', ['projectId' => $projectId]), $data, []);
    }

    /** @phpstan-return ApiObject */
    public function delete(string $projectId): array
    {
        return $this->deleteArray($this->path('/projects/{projectId}', ['projectId' => $projectId]), []);
    }

    /** @phpstan-return ApiObject */
    public function rotateToken(string $projectId): array
    {
        return $this->postArray($this->path('/projects/{projectId}/rotate-token', ['projectId' => $projectId]), [], []);
    }

    /**
     * @phpstan-param UpdateProjectMembersData $data
     *
     * @phpstan-return ApiObject
     */
    public function updateMembers(string $projectId, array $data): array
    {
        return $this->putArray($this->path('/projects/{projectId}/members', ['projectId' => $projectId]), $data, []);
    }

    /** @phpstan-return ApiObject */
    public function addMember(string $projectId, string $teamMemberId): array
    {
        return $this->postArray($this->path('/projects/{projectId}/members/{teamMemberId}', [
            'projectId' => $projectId,
            'teamMemberId' => $teamMemberId,
        ]), [], []);
    }

    /** @phpstan-return ApiObject */
    public function removeMember(string $projectId, string $teamMemberId): array
    {
        return $this->deleteArray($this->path('/projects/{projectId}/members/{teamMemberId}', [
            'projectId' => $projectId,
            'teamMemberId' => $teamMemberId,
        ]), []);
    }

    /** @phpstan-return CursorPage */
    public function routes(string $projectId, array $query = []): array
    {
        return $this->getArray($this->path('/projects/{projectId}/routes', ['projectId' => $projectId]), $query);
    }

    /**
     * @phpstan-param StoreRouteData $data
     *
     * @phpstan-return ApiObject
     */
    public function createRoute(string $projectId, array $data): array
    {
        return $this->postArray($this->path('/projects/{projectId}/routes', ['projectId' => $projectId]), $data, []);
    }
}
