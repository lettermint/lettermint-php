<?php

declare(strict_types=1);

namespace Lettermint\Resources;

use Generator;
use Lettermint\Types\DeleteSuppressionResponse;
use Lettermint\Types\ListSuppressionsResponse;
use Lettermint\Types\SuppressedRecipientData;
use Lettermint\Types\SuppressionStoreResponse;

/**
 * The suppression list. Needs the team token.
 *
 * @phpstan-import-type ListSuppressionsQuery from \Lettermint\Types\ApiTypes
 * @phpstan-import-type StoreSuppressionData from \Lettermint\Types\ApiTypes
 */
final class Suppressions extends Resource
{
    /**
     * Lists suppressions, one page at a time.
     *
     * @param  ListSuppressionsQuery  $query
     */
    public function list(array $query = []): ListSuppressionsResponse
    {
        return $this->transport->object(ListSuppressionsResponse::class, 'GET /suppressions', 'suppressions.list', query: $query);
    }

    /**
     * Iterates over every suppression, following `next_cursor`.
     *
     * @param  ListSuppressionsQuery  $query
     * @return Generator<int, SuppressedRecipientData, mixed, void>
     */
    public function iterate(array $query = []): Generator
    {
        return $this->transport->paginate(ListSuppressionsResponse::class, 'GET /suppressions', 'suppressions.iterate', query: $query);
    }

    /**
     * @param  StoreSuppressionData  $body
     */
    public function create(array $body): SuppressionStoreResponse
    {
        return $this->transport->object(SuppressionStoreResponse::class, 'POST /suppressions', 'suppressions.create', json: $body);
    }

    /**
     * Removes a suppression (HTTP 200), or opens a review for one that needs it (HTTP 202).
     */
    public function delete(string $suppressionId): DeleteSuppressionResponse
    {
        return $this->transport->object(DeleteSuppressionResponse::class, 'DELETE /suppressions/{suppressionId}', 'suppressions.delete', ['suppressionId' => $suppressionId]);
    }
}
