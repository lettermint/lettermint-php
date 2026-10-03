<?php

declare(strict_types=1);

namespace Lettermint\Tests\Support;

use Psr\Http\Message\RequestInterface;

/**
 * A request the FakeApi received.
 */
final class RecordedRequest
{
    public readonly string $method;

    public readonly string $url;

    /** The path without the `/v1` base path. */
    public readonly string $path;

    /** @var array<string, string> Decoded query parameters by wire name. */
    public readonly array $query;

    /** @var array<string, string> Header values by lower-case name. */
    public readonly array $headers;

    public readonly string $rawBody;

    /**
     * @param  array<string, mixed>  $options  The Guzzle request options.
     */
    public function __construct(public readonly RequestInterface $request, public readonly array $options)
    {
        $this->method = $request->getMethod();
        $this->url = (string) $request->getUri();
        $this->path = (string) preg_replace('#^/v1#', '', $request->getUri()->getPath());
        $query = [];
        foreach (array_filter(explode('&', $request->getUri()->getQuery())) as $pair) {
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, '');
            $query[urldecode($key)] = urldecode($value);
        }
        $this->query = $query;
        $headers = [];
        foreach ($request->getHeaders() as $name => $values) {
            $headers[strtolower($name)] = implode(', ', $values);
        }
        $this->headers = $headers;
        $this->rawBody = (string) $request->getBody();
    }

    public function json(): mixed
    {
        return $this->rawBody === '' ? null : json_decode($this->rawBody, true, 512, JSON_THROW_ON_ERROR);
    }
}
