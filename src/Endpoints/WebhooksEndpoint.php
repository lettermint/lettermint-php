<?php

namespace Lettermint\Endpoints;

use Lettermint\Responses\CreateWebhookResponse;
use Lettermint\Responses\DeleteWebhookResponse;
use Lettermint\Responses\RegenerateWebhookSecretResponse;
use Lettermint\Responses\TestWebhookResponse;
use Lettermint\Responses\UpdateWebhookResponse;
use Lettermint\Responses\WebhookDeliveriesResponse;
use Lettermint\Responses\WebhookDeliveryResponse;
use Lettermint\Responses\WebhookListResponse;
use Lettermint\Responses\WebhookResponse;

/**
 * @phpstan-import-type StoreWebhookData from \Lettermint\Types\ApiTypes
 * @phpstan-import-type UpdateWebhookData from \Lettermint\Types\ApiTypes
 */
class WebhooksEndpoint extends Endpoint
{
    public function list(array $query = []): WebhookListResponse
    {
        return $this->hydrate(WebhookListResponse::class, $this->getArray($this->path('/webhooks'), $query));
    }

    /**
     * @phpstan-param StoreWebhookData $data
     */
    public function create(array $data): CreateWebhookResponse
    {
        return $this->hydrate(CreateWebhookResponse::class, $this->postArray($this->path('/webhooks'), $data, []));
    }

    public function retrieve(string $webhookId): WebhookResponse
    {
        return $this->hydrate(WebhookResponse::class, $this->getArray($this->path('/webhooks/{webhookId}', ['webhookId' => $webhookId]), []));
    }

    /**
     * @phpstan-param UpdateWebhookData $data
     */
    public function update(string $webhookId, array $data): UpdateWebhookResponse
    {
        return $this->hydrate(UpdateWebhookResponse::class, $this->putArray($this->path('/webhooks/{webhookId}', ['webhookId' => $webhookId]), $data, []));
    }

    public function delete(string $webhookId): DeleteWebhookResponse
    {
        return $this->hydrate(DeleteWebhookResponse::class, $this->deleteArray($this->path('/webhooks/{webhookId}', ['webhookId' => $webhookId]), []));
    }

    public function test(string $webhookId): TestWebhookResponse
    {
        return $this->hydrate(TestWebhookResponse::class, $this->postArray($this->path('/webhooks/{webhookId}/test', ['webhookId' => $webhookId]), [], []));
    }

    public function regenerateSecret(string $webhookId): RegenerateWebhookSecretResponse
    {
        return $this->hydrate(RegenerateWebhookSecretResponse::class, $this->postArray($this->path('/webhooks/{webhookId}/regenerate-secret', ['webhookId' => $webhookId]), [], []));
    }

    public function deliveries(string $webhookId, array $query = []): WebhookDeliveriesResponse
    {
        return $this->hydrate(WebhookDeliveriesResponse::class, $this->getArray($this->path('/webhooks/{webhookId}/deliveries', ['webhookId' => $webhookId]), $query));
    }

    public function delivery(string $webhookId, string $deliveryId): WebhookDeliveryResponse
    {
        return $this->hydrate(WebhookDeliveryResponse::class, $this->getArray($this->path('/webhooks/{webhookId}/deliveries/{deliveryId}', [
            'webhookId' => $webhookId,
            'deliveryId' => $deliveryId,
        ]), []));
    }
}
