<?php

declare(strict_types=1);

namespace Lettermint;

use Generator;
use GuzzleHttp\ClientInterface;
use JsonSerializable;
use Lettermint\Exceptions\LettermintConfigException;
use Lettermint\Internal\Tokens;
use Lettermint\Internal\Transport;
use Lettermint\Resources\Domains;
use Lettermint\Resources\Emails;
use Lettermint\Resources\Messages;
use Lettermint\Resources\Projects;
use Lettermint\Resources\Routes;
use Lettermint\Resources\Stats;
use Lettermint\Resources\Suppressions;
use Lettermint\Resources\Team;
use Lettermint\Resources\Webhooks;
use Lettermint\Types\AnalyticsPagination;
use Lettermint\Types\AnalyticsResponse;
use Lettermint\Types\BlockedFileTypes;

/**
 * The Lettermint client.
 *
 * ```php
 * $lettermint = new Lettermint(sendingToken: getenv('LETTERMINT_PROJECT_TOKEN'));
 * $lettermint = new Lettermint(sendingToken: $projectToken, teamToken: $teamToken, timeout: 10);
 * $lettermint = new Lettermint('lm_...'); // a team or sending token, detected by its format
 * ```
 *
 * `emails` uses the sending token; every other part uses the team token. The
 * client holds no message state, so create it once and share it, also in
 * long-running workers.
 *
 * @phpstan-import-type AnalyticsQuery from \Lettermint\Types\ApiTypes
 */
final class Lettermint implements JsonSerializable
{
    public const DEFAULT_BASE_URL = 'https://api.lettermint.co/v1';

    /** The default request timeout in seconds. */
    public const DEFAULT_TIMEOUT = 30.0;

    /** Send email. Needs the sending token. */
    public readonly Emails $emails;

    /** Sending domains. Needs the team token. */
    public readonly Domains $domains;

    /** Sent and received messages. Needs the team token. */
    public readonly Messages $messages;

    /** Projects and their report forwarding. Needs the team token. */
    public readonly Projects $projects;

    /** Routes of a project. Needs the team token. */
    public readonly Routes $routes;

    /** Sending statistics. Needs the team token. */
    public readonly Stats $stats;

    /** The suppression list. Needs the team token. */
    public readonly Suppressions $suppressions;

    /** The team and its members. Needs the team token. */
    public readonly Team $team;

    /** Webhook endpoints and their deliveries. Needs the team token. */
    public readonly Webhooks $webhooks;

    private readonly Transport $transport;

    /**
     * Pass `sendingToken`, `teamToken` or both as named arguments, or one token
     * string as the first argument.
     *
     * @param  string|null  $token  A token whose type is detected by its format: `lm_team_…` is a team token, any other `lm_…` token a sending token.
     * @param  string|null  $sendingToken  A project sending token (`lm_…`), sent as `x-lettermint-token`. Used by `emails`.
     * @param  string|null  $teamToken  A team API token (`lm_team_…`), sent as `Authorization: Bearer`. Used by the Team API.
     * @param  string  $baseUrl  The API base URL, including its path (`/v1`).
     * @param  float  $timeout  Seconds before a request fails with a TimeoutException. Covers connecting, sending and reading the whole response.
     * @param  ClientInterface|null  $httpClient  A Guzzle client, for example with a proxy or test handler. The SDK still disables redirects and sets the timeout on every request.
     *
     * @throws LettermintConfigException When no token is given, a token is invalid, or an option is invalid.
     */
    public function __construct(
        #[\SensitiveParameter] ?string $token = null,
        #[\SensitiveParameter] ?string $sendingToken = null,
        #[\SensitiveParameter] ?string $teamToken = null,
        string $baseUrl = self::DEFAULT_BASE_URL,
        float $timeout = self::DEFAULT_TIMEOUT,
        ?ClientInterface $httpClient = null,
    ) {
        if ($token !== null) {
            if ($sendingToken !== null || $teamToken !== null) {
                throw new LettermintConfigException('Pass a token string or sendingToken/teamToken, not both.');
            }
            if (Tokens::detect($token) === 'team') {
                $teamToken = $token;
            } else {
                $sendingToken = $token;
            }
        }
        $sendingToken = Tokens::check('sendingToken', $sendingToken);
        $teamToken = Tokens::check('teamToken', $teamToken);
        if ($sendingToken === null && $teamToken === null) {
            throw new LettermintConfigException('Pass sendingToken, teamToken or both.');
        }
        if (! is_finite($timeout) || $timeout <= 0) {
            throw new LettermintConfigException('timeout must be a positive number of seconds.');
        }

        $this->transport = new Transport($sendingToken, $teamToken, self::checkBaseUrl($baseUrl), $timeout, $httpClient);
        $this->emails = new Emails($this->transport);
        $this->domains = new Domains($this->transport);
        $this->messages = new Messages($this->transport);
        $this->projects = new Projects($this->transport);
        $this->routes = new Routes($this->transport);
        $this->stats = new Stats($this->transport);
        $this->suppressions = new Suppressions($this->transport);
        $this->team = new Team($this->transport);
        $this->webhooks = new Webhooks($this->transport);
    }

    /**
     * Checks the configured token: `GET /ping` returns `pong`. Uses the team
     * token when configured, otherwise the sending token.
     */
    public function ping(): string
    {
        return trim($this->transport->text('GET /ping', 'ping'));
    }

    /**
     * Queries email analytics. Needs the team token.
     *
     * @param  AnalyticsQuery  $query
     */
    public function analytics(array $query): AnalyticsResponse
    {
        return $this->transport->object(AnalyticsResponse::class, 'POST /analytics', 'analytics', json: $query);
    }

    /**
     * Queries email analytics and follows `pagination.next_cursor`, yielding one
     * whole response per request. Each response carries the next page of
     * `data.breakdown` with its own `meta` and `pagination`. Needs the team token.
     *
     * A cursor expires 60 seconds after its response, so request the next page
     * promptly; an expired cursor throws a ValidationException. The token is
     * checked before the first page is requested; pages are requested only
     * when the caller gets to them.
     *
     * @param  AnalyticsQuery  $query
     * @return Generator<int, AnalyticsResponse, mixed, void>
     */
    public function analyticsPages(array $query): Generator
    {
        $this->transport->assertAuth('analyticsPages', 'team');

        return $this->analyticsResponses($query);
    }

    /**
     * The file extensions and MIME types that cannot be attached. Needs the team token.
     */
    public function blockedFileTypes(): BlockedFileTypes
    {
        return $this->transport->object(BlockedFileTypes::class, 'GET /blocked-file-types', 'blockedFileTypes');
    }

    /**
     * The configuration without credentials: tokens show as `[redacted]`.
     *
     * @return array{baseUrl: string, timeout: float, sendingToken: string|null, teamToken: string|null}
     */
    public function jsonSerialize(): array
    {
        return [
            'baseUrl' => $this->transport->baseUrl,
            'timeout' => $this->transport->timeout,
            'sendingToken' => $this->transport->hasSendingToken() ? '[redacted]' : null,
            'teamToken' => $this->transport->hasTeamToken() ? '[redacted]' : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return $this->jsonSerialize();
    }

    /**
     * @return never
     */
    public function __serialize(): array
    {
        throw new LettermintConfigException('A Lettermint client cannot be serialized, because it holds API tokens. Create it where you use it, for example from the service container.');
    }

    /**
     * @param  AnalyticsQuery  $query
     * @return Generator<int, AnalyticsResponse, mixed, void>
     */
    private function analyticsResponses(array $query): Generator
    {
        $current = $query;
        $seen = [];
        if (is_string($query['cursor'] ?? null)) {
            $seen[$query['cursor']] = true;
        }
        while (true) {
            $page = $this->transport->object(AnalyticsResponse::class, 'POST /analytics', 'analyticsPages', json: $current);
            yield $page;
            $pagination = $page->pagination;
            $next = $pagination instanceof AnalyticsPagination ? $pagination->next_cursor : null;
            if (! is_string($next) || $next === '' || isset($seen[$next])) {
                return;
            }
            $seen[$next] = true;
            $current = $query;
            $current['cursor'] = $next;
        }
    }

    private static function checkBaseUrl(string $baseUrl): string
    {
        $parts = parse_url($baseUrl);
        if ($parts === false || ! isset($parts['scheme'], $parts['host']) || ! in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            throw new LettermintConfigException('baseUrl must be an absolute http(s) URL.');
        }
        if (isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment']) || str_contains($baseUrl, '?') || str_contains($baseUrl, '#')) {
            throw new LettermintConfigException('baseUrl must not contain credentials, a query string or a fragment.');
        }
        if (preg_match('/[\x00-\x20\x7f]/', $baseUrl) === 1) {
            throw new LettermintConfigException('baseUrl must not contain whitespace or control characters.');
        }

        return rtrim($baseUrl, '/');
    }
}
