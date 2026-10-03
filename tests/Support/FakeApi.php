<?php

declare(strict_types=1);

namespace Lettermint\Tests\Support;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Throwable;

/**
 * A fake Lettermint API for a Guzzle client: records every request and
 * answers with the handler (default: `{}` with HTTP 200). The handler runs
 * behind Guzzle's default middleware stack, so redirects would be followed
 * if the SDK did not disable them.
 */
final class FakeApi
{
    /** @var list<RecordedRequest> */
    public array $requests = [];

    /** @var callable(RecordedRequest, int): mixed */
    private $handler;

    /**
     * @param  (callable(RecordedRequest, int): mixed)|null  $handler  Returns a response, or throws.
     */
    public function __construct(?callable $handler = null)
    {
        $this->handler = $handler ?? static fn (): Response => new Response(200, ['Content-Type' => 'application/json'], '{}');
    }

    public function client(): Client
    {
        $stack = HandlerStack::create(function (RequestInterface $request, array $options): PromiseInterface {
            $recorded = new RecordedRequest($request, $options);
            $this->requests[] = $recorded;
            try {
                $response = ($this->handler)($recorded, count($this->requests) - 1);
            } catch (Throwable $exception) {
                return Create::rejectionFor($exception);
            }

            return Create::promiseFor($response);
        });

        return new Client(['handler' => $stack]);
    }

    public function last(): RecordedRequest
    {
        return $this->requests[array_key_last($this->requests)];
    }
}
