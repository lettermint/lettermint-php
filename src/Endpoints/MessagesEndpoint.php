<?php

namespace Lettermint\Endpoints;

/**
 * @phpstan-import-type CursorPage from \Lettermint\Types\ApiTypes
 * @phpstan-import-type ApiObject from \Lettermint\Types\ApiTypes
 */
class MessagesEndpoint extends Endpoint
{
    /** @phpstan-return CursorPage|list<ApiObject> */
    public function list(array $query = []): array
    {
        return $this->getArray($this->path('/messages'), $query);
    }

    /** @phpstan-return ApiObject */
    public function retrieve(string $messageId): array
    {
        return $this->getArray($this->path('/messages/{messageId}', ['messageId' => $messageId]), []);
    }

    /** @phpstan-return ApiObject */
    public function events(string $messageId, array $query = []): array
    {
        return $this->getArray($this->path('/messages/{messageId}/events', ['messageId' => $messageId]), $query);
    }

    public function source(string $messageId): string
    {
        return $this->getRaw($this->path('/messages/{messageId}/source', ['messageId' => $messageId]), []);
    }

    public function html(string $messageId): string
    {
        return $this->getRaw($this->path('/messages/{messageId}/html', ['messageId' => $messageId]), []);
    }

    public function text(string $messageId): string
    {
        return $this->getRaw($this->path('/messages/{messageId}/text', ['messageId' => $messageId]), []);
    }
}
