<?php

declare(strict_types=1);

namespace Lettermint\Resources;

use Generator;
use Lettermint\Types\ListTeamApiTokensResponse;
use Lettermint\Types\MessageResponse;
use Lettermint\Types\TeamApiToken;
use Lettermint\Types\TeamApiTokenCreatedResponse;

/**
 * Team API tokens. Needs the team token.
 *
 * @phpstan-import-type ListTeamApiTokensQuery from \Lettermint\Types\ApiTypes
 * @phpstan-import-type StoreTeamApiTokenData from \Lettermint\Types\ApiTypes
 */
final class TeamApiTokens extends Resource
{
    /**
     * Lists the team's API tokens, one page at a time. The tokens' values are
     * not included; they are only returned by create().
     *
     * @param  ListTeamApiTokensQuery  $query
     */
    public function list(array $query = []): ListTeamApiTokensResponse
    {
        return $this->transport->object(ListTeamApiTokensResponse::class, 'GET /team/api-tokens', 'team.apiTokens.list', query: $query);
    }

    /**
     * Iterates over every team API token, following `next_cursor`.
     *
     * @param  ListTeamApiTokensQuery  $query
     * @return Generator<int, TeamApiToken, mixed, void>
     */
    public function iterate(array $query = []): Generator
    {
        return $this->transport->paginate(ListTeamApiTokensResponse::class, 'GET /team/api-tokens', 'team.apiTokens.iterate', query: $query);
    }

    /**
     * Creates a team API token. The new token can only have abilities that the
     * requesting token has. It copies the requesting token's IP allowlist, and
     * it can't outlive the requesting token. The response holds the token's
     * value once (`data->token`); store it, because it cannot be read again.
     *
     * @param  StoreTeamApiTokenData  $body
     */
    public function create(array $body): TeamApiTokenCreatedResponse
    {
        return $this->transport->object(TeamApiTokenCreatedResponse::class, 'POST /team/api-tokens', 'team.apiTokens.create', json: $body);
    }

    /**
     * Deletes a team API token. The token stops working immediately. A token
     * can only delete tokens whose abilities it also has.
     */
    public function delete(string $tokenId): MessageResponse
    {
        return $this->transport->object(MessageResponse::class, 'DELETE /team/api-tokens/{tokenId}', 'team.apiTokens.delete', ['tokenId' => $tokenId]);
    }
}
