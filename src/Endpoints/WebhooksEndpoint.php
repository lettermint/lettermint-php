<?php

namespace Lettermint\Endpoints;

/**
 * @phpstan-import-type CursorPage from \Lettermint\Types\ApiTypes
 * @phpstan-import-type ApiObject from \Lettermint\Types\ApiTypes
 * @phpstan-import-type StoreWebhookData from \Lettermint\Types\ApiTypes
 * @phpstan-import-type UpdateWebhookData from \Lettermint\Types\ApiTypes
 */
class WebhooksEndpoint extends Endpoint
{
    /** @phpstan-return CursorPage */
    public function list(array $query = []): array
    {
        return $this->getArray($this->path('/webhooks'), $query);
    }

    /**
     * @phpstan-param StoreWebhookData $data
     *
     * @phpstan-return ApiObject
     */
    public function create(array $data): array
    {
        return $this->postArray($this->path('/webhooks'), $data, []);
    }

    /** @phpstan-return ApiObject */
    public function retrieve(string $webhookId): array
    {
        return $this->getArray($this->path('/webhooks/{webhookId}', ['webhookId' => $webhookId]), []);
    }

    /**
     * @phpstan-param UpdateWebhookData $data
     *
     * @phpstan-return ApiObject
     */
    public function update(string $webhookId, array $data): array
    {
        return $this->putArray($this->path('/webhooks/{webhookId}', ['webhookId' => $webhookId]), $data, []);
    }

    /** @phpstan-return ApiObject */
    public function delete(string $webhookId): array
    {
        return $this->deleteArray($this->path('/webhooks/{webhookId}', ['webhookId' => $webhookId]), []);
    }

    /** @phpstan-return ApiObject */
    public function test(string $webhookId): array
    {
        return $this->postArray($this->path('/webhooks/{webhookId}/test', ['webhookId' => $webhookId]), [], []);
    }

    /** @phpstan-return ApiObject */
    public function regenerateSecret(string $webhookId): array
    {
        return $this->postArray($this->path('/webhooks/{webhookId}/regenerate-secret', ['webhookId' => $webhookId]), [], []);
    }

    /** @phpstan-return CursorPage */
    public function deliveries(string $webhookId, array $query = []): array
    {
        return $this->getArray($this->path('/webhooks/{webhookId}/deliveries', ['webhookId' => $webhookId]), $query);
    }

    /** @phpstan-return ApiObject */
    public function delivery(string $webhookId, string $deliveryId): array
    {
        return $this->getArray($this->path('/webhooks/{webhookId}/deliveries/{deliveryId}', [
            'webhookId' => $webhookId,
            'deliveryId' => $deliveryId,
        ]), []);
    }
}
