<?php

namespace Lettermint\Endpoints;

use Lettermint\Responses\CreateProjectResponse;
use Lettermint\Responses\CreateRouteResponse;
use Lettermint\Responses\DeleteProjectResponse;
use Lettermint\Responses\ProjectListResponse;
use Lettermint\Responses\ProjectMemberResponse;
use Lettermint\Responses\ProjectResponse;
use Lettermint\Responses\ProjectRoutesResponse;
use Lettermint\Responses\RotateProjectTokenResponse;
use Lettermint\Responses\UpdateProjectMembersResponse;
use Lettermint\Responses\UpdateProjectResponse;

/**
 * @phpstan-import-type StoreProjectData from \Lettermint\Types\ApiTypes
 * @phpstan-import-type UpdateProjectData from \Lettermint\Types\ApiTypes
 * @phpstan-import-type UpdateProjectMembersData from \Lettermint\Types\ApiTypes
 * @phpstan-import-type StoreRouteData from \Lettermint\Types\ApiTypes
 */
class ProjectsEndpoint extends Endpoint
{
    public function list(array $query = []): ProjectListResponse
    {
        return $this->hydrate(ProjectListResponse::class, $this->getArray($this->path('/projects'), $query));
    }

    /**
     * @phpstan-param StoreProjectData $data
     */
    public function create(array $data): CreateProjectResponse
    {
        return $this->hydrate(CreateProjectResponse::class, $this->postArray($this->path('/projects'), $data, []));
    }

    public function retrieve(string $projectId, array $query = []): ProjectResponse
    {
        return $this->hydrate(ProjectResponse::class, $this->getArray($this->path('/projects/{projectId}', ['projectId' => $projectId]), $query));
    }

    /**
     * @phpstan-param UpdateProjectData $data
     */
    public function update(string $projectId, array $data): UpdateProjectResponse
    {
        return $this->hydrate(UpdateProjectResponse::class, $this->putArray($this->path('/projects/{projectId}', ['projectId' => $projectId]), $data, []));
    }

    public function delete(string $projectId): DeleteProjectResponse
    {
        return $this->hydrate(DeleteProjectResponse::class, $this->deleteArray($this->path('/projects/{projectId}', ['projectId' => $projectId]), []));
    }

    public function rotateToken(string $projectId): RotateProjectTokenResponse
    {
        return $this->hydrate(RotateProjectTokenResponse::class, $this->postArray($this->path('/projects/{projectId}/rotate-token', ['projectId' => $projectId]), [], []));
    }

    /**
     * @phpstan-param UpdateProjectMembersData $data
     */
    public function updateMembers(string $projectId, array $data): UpdateProjectMembersResponse
    {
        return $this->hydrate(UpdateProjectMembersResponse::class, $this->putArray($this->path('/projects/{projectId}/members', ['projectId' => $projectId]), $data, []));
    }

    public function addMember(string $projectId, string $teamMemberId): ProjectMemberResponse
    {
        return $this->hydrate(ProjectMemberResponse::class, $this->postArray($this->path('/projects/{projectId}/members/{teamMemberId}', [
            'projectId' => $projectId,
            'teamMemberId' => $teamMemberId,
        ]), [], []));
    }

    public function removeMember(string $projectId, string $teamMemberId): ProjectMemberResponse
    {
        return $this->hydrate(ProjectMemberResponse::class, $this->deleteArray($this->path('/projects/{projectId}/members/{teamMemberId}', [
            'projectId' => $projectId,
            'teamMemberId' => $teamMemberId,
        ]), []));
    }

    public function routes(string $projectId, array $query = []): ProjectRoutesResponse
    {
        return $this->hydrate(ProjectRoutesResponse::class, $this->getArray($this->path('/projects/{projectId}/routes', ['projectId' => $projectId]), $query));
    }

    /**
     * @phpstan-param StoreRouteData $data
     */
    public function createRoute(string $projectId, array $data): CreateRouteResponse
    {
        return $this->hydrate(CreateRouteResponse::class, $this->postArray($this->path('/projects/{projectId}/routes', ['projectId' => $projectId]), $data, []));
    }
}
