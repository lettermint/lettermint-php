<?php

namespace Lettermint\Endpoints;

use Lettermint\Responses\MessageEventsResponse;
use Lettermint\Responses\MessageListResponse;
use Lettermint\Responses\MessageResponse;

class MessagesEndpoint extends Endpoint
{
    public function list(array $query = []): MessageListResponse
    {
        return $this->hydrateList(MessageListResponse::class, $this->getArray($this->path('/messages'), $query));
    }

    public function retrieve(string $messageId): MessageResponse
    {
        return $this->hydrate(MessageResponse::class, $this->getArray($this->path('/messages/{messageId}', ['messageId' => $messageId]), []));
    }

    public function events(string $messageId, array $query = []): MessageEventsResponse
    {
        return $this->hydrate(MessageEventsResponse::class, $this->getArray($this->path('/messages/{messageId}/events', ['messageId' => $messageId]), $query));
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
