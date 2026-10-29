<?php

declare(strict_types=1);

namespace Lettermint\Resources;

use Generator;
use Lettermint\Types\ListProjectApiTokensResponse;
use Lettermint\Types\MessageResponse;
use Lettermint\Types\ProjectApiToken;
use Lettermint\Types\ProjectApiTokenCreatedResponse;

/**
 * Project API tokens (sending tokens) of a project. Needs the team token.
 *
 * @phpstan-import-type ListProjectApiTokensQuery from \Lettermint\Types\ApiTypes
 * @phpstan-import-type StoreProjectApiTokenData from \Lettermint\Types\ApiTypes
 */
final class ProjectApiTokens extends Resource
{
    /**
     * Lists the API tokens of a project, one page at a time. The tokens'
     * values are not included; they are only returned by create().
     *
     * @param  ListProjectApiTokensQuery  $query
     */
    public function list(string $projectId, array $query = []): ListProjectApiTokensResponse
    {
        return $this->transport->object(ListProjectApiTokensResponse::class, 'GET /projects/{projectId}/api-tokens', 'projects.apiTokens.list', ['projectId' => $projectId], $query);
    }

    /**
     * Iterates over every API token of a project, following `next_cursor`.
     *
     * @param  ListProjectApiTokensQuery  $query
     * @return Generator<int, ProjectApiToken, mixed, void>
     */
    public function iterate(string $projectId, array $query = []): Generator
    {
        return $this->transport->paginate(ListProjectApiTokensResponse::class, 'GET /projects/{projectId}/api-tokens', 'projects.apiTokens.iterate', ['projectId' => $projectId], $query);
    }

    /**
     * Creates an API token for the project. The response holds the token's
     * value once (`data->token`); store it, because it cannot be read again.
     * Use it to send through the API, or as the SMTP password when SMTP is
     * enabled for the project (`smtp` then holds the SMTP settings).
     *
     * @param  StoreProjectApiTokenData  $body
     */
    public function create(string $projectId, array $body): ProjectApiTokenCreatedResponse
    {
        return $this->transport->object(ProjectApiTokenCreatedResponse::class, 'POST /projects/{projectId}/api-tokens', 'projects.apiTokens.create', ['projectId' => $projectId], json: $body);
    }

    /**
     * Deletes an API token of the project. The token stops working immediately.
     */
    public function delete(string $projectId, string $tokenId): MessageResponse
    {
        return $this->transport->object(MessageResponse::class, 'DELETE /projects/{projectId}/api-tokens/{tokenId}', 'projects.apiTokens.delete', ['projectId' => $projectId, 'tokenId' => $tokenId]);
    }
}
