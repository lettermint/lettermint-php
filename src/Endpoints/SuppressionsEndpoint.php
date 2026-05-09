<?php

namespace Lettermint\Endpoints;

/**
 * @phpstan-import-type CursorPage from \Lettermint\Types\ApiTypes
 * @phpstan-import-type ApiObject from \Lettermint\Types\ApiTypes
 * @phpstan-import-type StoreSuppressionData from \Lettermint\Types\ApiTypes
 */
class SuppressionsEndpoint extends Endpoint
{
    /** @phpstan-return CursorPage */
    public function list(array $query = []): array
    {
        return $this->getArray($this->path('/suppressions'), $query);
    }

    /**
     * @phpstan-param StoreSuppressionData $data
     *
     * @phpstan-return ApiObject
     */
    public function create(array $data): array
    {
        return $this->postArray($this->path('/suppressions'), $data, []);
    }

    /** @phpstan-return ApiObject */
    public function delete(string $suppressionId): array
    {
        return $this->deleteArray($this->path('/suppressions/{suppressionId}', ['suppressionId' => $suppressionId]), []);
    }
}
