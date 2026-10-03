<?php

declare(strict_types=1);

namespace Lettermint\Resources;

use Generator;
use Lettermint\Internal\Transport;
use Lettermint\Types\ListWebhooksResponse;
use Lettermint\Types\MessageResponse;
use Lettermint\Types\TestWebhookResponse;
use Lettermint\Types\WebhookData;
use Lettermint\Types\WebhookListData;
use Lettermint\Types\WebhookMutationResponse;
use Lettermint\Types\WebhookSecretResponse;

/**
 * Webhook endpoints. Needs the team token. To verify incoming deliveries, use
 * Lettermint\Webhook.
 *
 * @phpstan-import-type ListWebhooksQuery from \Lettermint\Types\ApiTypes
 * @phpstan-import-type StoreWebhookData from \Lettermint\Types\ApiTypes
 * @phpstan-import-type UpdateWebhookData from \Lettermint\Types\ApiTypes
 */
final class Webhooks extends Resource
{
    /** Delivery attempts of a webhook. */
    public readonly WebhookDeliveries $deliveries;

    /**
     * @internal Use $lettermint->webhooks.
     */
    public function __construct(Transport $transport)
    {
        parent::__construct($transport);
        $this->deliveries = new WebhookDeliveries($transport);
    }

    /**
     * Lists webhooks, one page at a time.
     *
     * @param  ListWebhooksQuery  $query
     */
    public function list(array $query = []): ListWebhooksResponse
    {
        return $this->transport->object(ListWebhooksResponse::class, 'GET /webhooks', 'webhooks.list', query: $query);
    }

    /**
     * Iterates over every webhook, following `next_cursor`.
     *
     * @param  ListWebhooksQuery  $query
     * @return Generator<int, WebhookListData, mixed, void>
     */
    public function iterate(array $query = []): Generator
    {
        return $this->transport->paginate(ListWebhooksResponse::class, 'GET /webhooks', 'webhooks.iterate', query: $query);
    }

    /**
     * Creates a webhook. The response holds its signing secret once.
     *
     * @param  StoreWebhookData  $body
     */
    public function create(array $body): WebhookSecretResponse
    {
        return $this->transport->object(WebhookSecretResponse::class, 'POST /webhooks', 'webhooks.create', json: $body);
    }

    public function retrieve(string $webhookId): WebhookData
    {
        return $this->transport->object(WebhookData::class, 'GET /webhooks/{webhookId}', 'webhooks.retrieve', ['webhookId' => $webhookId]);
    }

    /**
     * @param  UpdateWebhookData  $body
     */
    public function update(string $webhookId, array $body): WebhookMutationResponse
    {
        return $this->transport->object(WebhookMutationResponse::class, 'PUT /webhooks/{webhookId}', 'webhooks.update', ['webhookId' => $webhookId], json: $body);
    }

    public function delete(string $webhookId): MessageResponse
    {
        return $this->transport->object(MessageResponse::class, 'DELETE /webhooks/{webhookId}', 'webhooks.delete', ['webhookId' => $webhookId]);
    }

    /**
     * Sends a `webhook.test` delivery.
     */
    public function test(string $webhookId): TestWebhookResponse
    {
        return $this->transport->object(TestWebhookResponse::class, 'POST /webhooks/{webhookId}/test', 'webhooks.test', ['webhookId' => $webhookId]);
    }

    /**
     * Replaces the signing secret. The response holds the new secret once.
     */
    public function regenerateSecret(string $webhookId): WebhookSecretResponse
    {
        return $this->transport->object(WebhookSecretResponse::class, 'POST /webhooks/{webhookId}/regenerate-secret', 'webhooks.regenerateSecret', ['webhookId' => $webhookId]);
    }
}
