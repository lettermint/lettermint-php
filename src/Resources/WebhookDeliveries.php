<?php

declare(strict_types=1);

namespace Lettermint\Resources;

use Generator;
use Lettermint\Types\ListWebhookDeliveriesResponse;
use Lettermint\Types\WebhookDeliveryData;
use Lettermint\Types\WebhookDeliveryListData;

/**
 * Delivery attempts of a webhook. Needs the team token.
 *
 * @phpstan-import-type ListWebhookDeliveriesQuery from \Lettermint\Types\ApiTypes
 */
final class WebhookDeliveries extends Resource
{
    /**
     * Lists the deliveries of a webhook, one page at a time.
     *
     * @param  ListWebhookDeliveriesQuery  $query
     */
    public function list(string $webhookId, array $query = []): ListWebhookDeliveriesResponse
    {
        return $this->transport->object(ListWebhookDeliveriesResponse::class, 'GET /webhooks/{webhookId}/deliveries', 'webhooks.deliveries.list', ['webhookId' => $webhookId], $query);
    }

    /**
     * Iterates over every delivery of a webhook, following `next_cursor`.
     *
     * @param  ListWebhookDeliveriesQuery  $query
     * @return Generator<int, WebhookDeliveryListData, mixed, void>
     */
    public function iterate(string $webhookId, array $query = []): Generator
    {
        return $this->transport->paginate(ListWebhookDeliveriesResponse::class, 'GET /webhooks/{webhookId}/deliveries', 'webhooks.deliveries.iterate', ['webhookId' => $webhookId], $query);
    }

    public function retrieve(string $webhookId, string $deliveryId): WebhookDeliveryData
    {
        return $this->transport->object(WebhookDeliveryData::class, 'GET /webhooks/{webhookId}/deliveries/{deliveryId}', 'webhooks.deliveries.retrieve', ['webhookId' => $webhookId, 'deliveryId' => $deliveryId]);
    }
}
