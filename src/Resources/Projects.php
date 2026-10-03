<?php

declare(strict_types=1);

namespace Lettermint\Resources;

use Generator;
use Lettermint\Internal\Transport;
use Lettermint\Types\ListProjectsResponse;
use Lettermint\Types\MessageResponse;
use Lettermint\Types\ProjectCreatedData;
use Lettermint\Types\ProjectData;
use Lettermint\Types\ProjectListData;
use Lettermint\Types\ProjectMutationResponse;
use Lettermint\Types\RotateProjectTokenResponse;

/**
 * Projects. Needs the team token.
 *
 * @phpstan-import-type ListProjectsQuery from \Lettermint\Types\ApiTypes
 * @phpstan-import-type GetProjectQuery from \Lettermint\Types\ApiTypes
 * @phpstan-import-type StoreProjectData from \Lettermint\Types\ApiTypes
 * @phpstan-import-type UpdateProjectData from \Lettermint\Types\ApiTypes
 */
final class Projects extends Resource
{
    /** Report forwarding of a project. */
    public readonly ReportForwarding $reportForwarding;

    /**
     * @internal Use $lettermint->projects.
     */
    public function __construct(Transport $transport)
    {
        parent::__construct($transport);
        $this->reportForwarding = new ReportForwarding($transport);
    }

    /**
     * Lists projects, one page at a time.
     *
     * @param  ListProjectsQuery  $query
     */
    public function list(array $query = []): ListProjectsResponse
    {
        return $this->transport->object(ListProjectsResponse::class, 'GET /projects', 'projects.list', query: $query);
    }

    /**
     * Iterates over every project, following `next_cursor`.
     *
     * @param  ListProjectsQuery  $query
     * @return Generator<int, ProjectListData, mixed, void>
     */
    public function iterate(array $query = []): Generator
    {
        return $this->transport->paginate(ListProjectsResponse::class, 'GET /projects', 'projects.iterate', query: $query);
    }

    /**
     * Creates a project. The response holds its sending token once (`api_token`).
     *
     * @param  StoreProjectData  $body
     */
    public function create(array $body): ProjectCreatedData
    {
        return $this->transport->object(ProjectCreatedData::class, 'POST /projects', 'projects.create', json: $body);
    }

    /**
     * @param  GetProjectQuery  $query
     */
    public function retrieve(string $projectId, array $query = []): ProjectData
    {
        return $this->transport->object(ProjectData::class, 'GET /projects/{projectId}', 'projects.retrieve', ['projectId' => $projectId], $query);
    }

    /**
     * @param  UpdateProjectData  $body
     */
    public function update(string $projectId, array $body): ProjectMutationResponse
    {
        return $this->transport->object(ProjectMutationResponse::class, 'PUT /projects/{projectId}', 'projects.update', ['projectId' => $projectId], json: $body);
    }

    public function delete(string $projectId): MessageResponse
    {
        return $this->transport->object(MessageResponse::class, 'DELETE /projects/{projectId}', 'projects.delete', ['projectId' => $projectId]);
    }

    /**
     * Rotates the project's legacy sending token.
     *
     * @deprecated The API marks this endpoint as legacy.
     */
    public function rotateToken(string $projectId): RotateProjectTokenResponse
    {
        return $this->transport->object(RotateProjectTokenResponse::class, 'POST /projects/{projectId}/rotate-token', 'projects.rotateToken', ['projectId' => $projectId]);
    }
}
