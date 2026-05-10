<?php

namespace Lettermint\Endpoints;

use Lettermint\Responses\CreateSuppressionResponse;
use Lettermint\Responses\DeleteSuppressionResponse;
use Lettermint\Responses\SuppressionListResponse;

/**
 * @phpstan-import-type StoreSuppressionData from \Lettermint\Types\ApiTypes
 */
class SuppressionsEndpoint extends Endpoint
{
    public function list(array $query = []): SuppressionListResponse
    {
        return $this->hydrate(SuppressionListResponse::class, $this->getArray($this->path('/suppressions'), $query));
    }

    /**
     * @phpstan-param StoreSuppressionData $data
     */
    public function create(array $data): CreateSuppressionResponse
    {
        return $this->hydrate(CreateSuppressionResponse::class, $this->postArray($this->path('/suppressions'), $data, []));
    }

    public function delete(string $suppressionId): DeleteSuppressionResponse
    {
        return $this->hydrate(DeleteSuppressionResponse::class, $this->deleteArray($this->path('/suppressions/{suppressionId}', ['suppressionId' => $suppressionId]), []));
    }
}
