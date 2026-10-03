<?php

declare(strict_types=1);

namespace Lettermint\Internal;

use Composer\InstalledVersions;
use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Lettermint\ApiObject;
use Lettermint\Exceptions\ApiException;
use Lettermint\Exceptions\AuthenticationException;
use Lettermint\Exceptions\ConflictException;
use Lettermint\Exceptions\ConnectionException;
use Lettermint\Exceptions\LettermintConfigException;
use Lettermint\Exceptions\LettermintValidationException;
use Lettermint\Exceptions\NotFoundException;
use Lettermint\Exceptions\PermissionException;
use Lettermint\Exceptions\RateLimitException;
use Lettermint\Exceptions\RedirectException;
use Lettermint\Exceptions\ServerException;
use Lettermint\Exceptions\TimeoutException;
use Lettermint\Exceptions\UnexpectedResponseException;
use Lettermint\Exceptions\ValidationException;
use Lettermint\Types\CursorPage;
use Lettermint\Types\Operations;
use Psr\Http\Message\ResponseInterface;
use SensitiveParameterValue;

/**
 * Sends the requests of one client. Holds the tokens, wrapped in
 * SensitiveParameterValue so that no debug or export function shows them.
 *
 * Every request is sent with redirects disabled, a total timeout and without
 * retries. Guzzle exceptions are never attached to SDK exceptions, because
 * they hold the request and its headers.
 *
 * @internal
 */
final class Transport
{
    private const CURLE_OPERATION_TIMEDOUT = 28;

    /** The timeout exceptions of Guzzle 8. */
    private const GUZZLE_TIMEOUT_EXCEPTIONS = [
        'GuzzleHttp\\Exception\\ConnectTimeoutException',
        'GuzzleHttp\\Exception\\NetworkTimeoutException',
        'GuzzleHttp\\Exception\\ResponseTimeoutException',
    ];

    private readonly ?SensitiveParameterValue $sendingToken;

    private readonly ?SensitiveParameterValue $teamToken;

    private readonly ClientInterface $http;

    private readonly string $userAgent;

    public function __construct(
        #[\SensitiveParameter] ?string $sendingToken,
        #[\SensitiveParameter] ?string $teamToken,
        public readonly string $baseUrl,
        public readonly float $timeout,
        ?ClientInterface $http = null,
    ) {
        $this->sendingToken = $sendingToken === null ? null : new SensitiveParameterValue($sendingToken);
        $this->teamToken = $teamToken === null ? null : new SensitiveParameterValue($teamToken);
        $this->http = $http ?? new Client;
        $this->userAgent = sprintf('lettermint-php/%s (PHP %s)', self::version(), PHP_VERSION);
    }

    public function hasSendingToken(): bool
    {
        return $this->sendingToken !== null;
    }

    public function hasTeamToken(): bool
    {
        return $this->teamToken !== null;
    }

    /**
     * Throws if the token that `$auth` needs is not configured.
     *
     * @param  'either'|'sending'|'team'  $auth
     */
    public function assertAuth(string $label, string $auth): void
    {
        $this->useTeamToken($label, $auth);
    }

    /**
     * Sends an operation and hydrates the JSON object it returns.
     *
     * @template T of ApiObject
     *
     * @param  class-string<T>  $class
     * @param  array<string, string>  $path
     * @param  array<array-key, mixed>  $query
     * @param  array<array-key, mixed>|null  $json
     * @param  'either'|'sending'|'team'|null  $auth
     * @return T
     */
    public function object(string $class, string $key, string $label, array $path = [], array $query = [], ?array $json = null, ?string $idempotencyKey = null, ?string $auth = null): ApiObject
    {
        [$status, $data, $text] = $this->send($key, $label, $path, $query, $json, $idempotencyKey, $auth);

        return $this->hydrate($class, $label, $status, $data, $text);
    }

    /**
     * Sends an operation that returns a JSON list of objects.
     *
     * @template T of ApiObject
     *
     * @param  class-string<T>  $class
     * @param  array<array-key, mixed>|null  $json
     * @return list<T>
     */
    public function objects(string $class, string $key, string $label, ?array $json = null, ?string $idempotencyKey = null): array
    {
        [$status, $data, $text] = $this->send($key, $label, [], [], $json, $idempotencyKey, null);
        if (! is_array($data) || ! array_is_list($data)) {
            throw new UnexpectedResponseException("{$label}: the Lettermint API answered with HTTP {$status} and a body that is not a JSON list.", $status, $text);
        }

        return array_map(fn (mixed $item): ApiObject => $this->hydrate($class, $label, $status, $item, $text), $data);
    }

    /**
     * Sends an operation that returns text (`text/html`, `message/rfc822`, ...).
     *
     * @param  array<string, string>  $path
     * @param  'either'|'sending'|'team'|null  $auth
     */
    public function text(string $key, string $label, array $path = [], ?string $auth = null): string
    {
        [, $data] = $this->send($key, $label, $path, [], null, null, $auth);

        return is_string($data) ? $data : '';
    }

    /**
     * Sends an operation without a response body (HTTP 204).
     *
     * @param  array<string, string>  $path
     */
    public function none(string $key, string $label, array $path = []): void
    {
        $this->send($key, $label, $path, [], null, null, null);
    }

    /**
     * Follows `next_cursor` through every page of a cursor-paginated list. The
     * token and the path parameters are checked before the first page is
     * requested; pages are requested only when the caller gets to them.
     *
     * @template T
     *
     * @param  class-string<CursorPage<T>>  $class
     * @param  array<string, string>  $path
     * @param  array<array-key, mixed>  $query
     * @return Generator<int, T, mixed, void>
     */
    public function paginate(string $class, string $key, string $label, array $path = [], array $query = []): Generator
    {
        $operation = Operations::ALL[$key];
        $cursorParam = $operation['pagination']['cursorParam'] ?? null;
        if ($cursorParam === null) {
            throw new \LogicException("{$key} is not cursor-paginated.");
        }
        $this->useTeamToken($label, $operation['auth']);
        $this->path($operation['path'], $label, $path);

        return $this->pages($class, $key, $label, $path, $query, $cursorParam);
    }

    /**
     * @template T
     *
     * @param  class-string<CursorPage<T>>  $class
     * @param  array<string, string>  $path
     * @param  array<array-key, mixed>  $query
     * @return Generator<int, T, mixed, void>
     */
    private function pages(string $class, string $key, string $label, array $path, array $query, string $cursorParam): Generator
    {
        $current = $query;
        $seen = [];
        while (true) {
            [$status, $data, $text] = $this->send($key, $label, $path, $current, null, null, null);
            $page = $this->hydrate($class, $label, $status, $data, $text);
            $items = $page->data;
            if (! is_array($items) || ! array_is_list($items)) {
                throw new UnexpectedResponseException("{$label}: the Lettermint API returned a page without a data list.", $status, $text);
            }
            yield from $items;
            $next = $page->next_cursor;
            if (! is_string($next) || $next === '' || isset($seen[$next])) {
                return;
            }
            $seen[$next] = true;
            $current = Query::with($query, $cursorParam, $next);
        }
    }

    /**
     * @template T of ApiObject
     *
     * @param  class-string<T>  $class
     * @return T
     */
    private function hydrate(string $class, string $label, int $status, mixed $data, string $text): ApiObject
    {
        if (! is_array($data) || ($data !== [] && array_is_list($data))) {
            throw new UnexpectedResponseException("{$label}: the Lettermint API answered with HTTP {$status} and a body that is not a JSON object.", $status, $text);
        }

        return $class::from($data);
    }

    /**
     * Sends one request. Returns the status, the decoded body (`null` for no
     * body, a string for text operations) and the raw body.
     *
     * @param  array<string, string>  $path
     * @param  array<array-key, mixed>  $query
     * @param  array<array-key, mixed>|null  $json
     * @param  'either'|'sending'|'team'|null  $auth
     * @return array{0: int, 1: mixed, 2: string}
     */
    private function send(string $key, string $label, array $path, array $query, ?array $json, ?string $idempotencyKey, ?string $auth): array
    {
        $operation = Operations::ALL[$key];
        $useTeam = $this->useTeamToken($label, $auth ?? $operation['auth']);
        $url = $this->baseUrl.$this->path($operation['path'], $label, $path);
        $queryString = Query::serialize($query);
        if ($queryString !== '') {
            $url .= '?'.$queryString;
        }
        MessageValidator::idempotencyKey($idempotencyKey);

        $headers = ['Accept' => 'application/json', 'User-Agent' => $this->userAgent];
        if ($idempotencyKey !== null) {
            $headers['Idempotency-Key'] = $idempotencyKey;
        }
        $options = [
            'allow_redirects' => false,
            'http_errors' => false,
            'timeout' => $this->timeout,
            'connect_timeout' => $this->timeout,
            'read_timeout' => $this->timeout,
        ];
        if ($json !== null) {
            $headers['Content-Type'] = 'application/json';
            try {
                // An empty PHP array is an empty JSON object here: every request body but a batch is an object.
                $options['body'] = json_encode($json === [] && $key !== 'POST /send/batch' ? new \stdClass : $json, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
            } catch (JsonException $exception) {
                throw new LettermintValidationException("{$label}: the request body cannot be encoded as JSON ({$exception->getMessage()}).", 'body');
            }
        }
        $options['headers'] = $headers + $this->authHeader($useTeam);

        $started = microtime(true);
        try {
            $response = $this->http->request($operation['method'], $url, $options);
            $text = (string) $response->getBody();
        } catch (GuzzleException $exception) {
            // Build the SDK exception here, from strings only: a helper that took
            // the Guzzle exception as an argument would put it (and the request
            // with its token header) into the new exception's stack trace.
            $reason = $exception->getMessage();
            $timedOut = $this->isTimeout($exception, microtime(true) - $started);
            unset($exception);
            if ($timedOut) {
                throw new TimeoutException($this->timeout);
            }

            throw new ConnectionException('Could not reach the Lettermint API: '.$reason);
        } catch (\RuntimeException $exception) {
            // Reading the body stream failed.
            throw new ConnectionException('Could not read the response of the Lettermint API: '.$exception->getMessage());
        }

        return $this->decode($operation['response']['type'], $label, $response, $text);
    }

    /**
     * @param  'either'|'sending'|'team'  $auth
     */
    private function useTeamToken(string $label, string $auth): bool
    {
        $useTeam = $auth === 'team' || ($auth === 'either' && $this->teamToken !== null);
        if ($useTeam && $this->teamToken === null) {
            throw new LettermintConfigException("{$label} needs teamToken; pass it as new Lettermint(teamToken: ...).");
        }
        if (! $useTeam && $this->sendingToken === null) {
            throw new LettermintConfigException("{$label} needs sendingToken; pass it as new Lettermint(sendingToken: ...).");
        }

        return $useTeam;
    }

    /**
     * @return array<string, string>
     */
    private function authHeader(bool $useTeam): array
    {
        if ($useTeam) {
            /** @var string $token */
            $token = $this->teamToken?->getValue();

            return ['Authorization' => 'Bearer '.$token];
        }
        /** @var string $token */
        $token = $this->sendingToken?->getValue();

        return ['x-lettermint-token' => $token];
    }

    /**
     * Replaces `{name}` placeholders with URL-encoded path parameters. Rejects
     * empty values, `.` and `..`, which would change the path.
     *
     * @param  array<string, string>  $values
     */
    private function path(string $template, string $label, array $values): string
    {
        return (string) preg_replace_callback('/\{(\w+)\}/', static function (array $match) use ($label, $values): string {
            $value = $values[$match[1]] ?? null;
            if (! is_string($value) || $value === '' || $value === '.' || $value === '..') {
                throw new LettermintConfigException("{$label}: {$match[1]} must be a non-empty string other than \".\" and \"..\".");
            }

            return rawurlencode($value);
        }, $template);
    }

    /**
     * @return array{0: int, 1: mixed, 2: string}
     */
    private function decode(string $type, string $label, ResponseInterface $response, string $text): array
    {
        $status = $response->getStatusCode();
        if ($status >= 300 && $status < 400) {
            throw new RedirectException($status);
        }
        if ($status >= 200 && $status < 300) {
            if ($type === 'empty') {
                return [$status, null, $text];
            }
            if ($type === 'text') {
                return [$status, $text, $text];
            }
            if (trim($text) === '') {
                throw new UnexpectedResponseException("{$label}: the Lettermint API answered with HTTP {$status} and an empty body where JSON was expected.", $status, $text);
            }
            try {
                return [$status, json_decode($text, true, 512, JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING), $text];
            } catch (JsonException) {
                throw new UnexpectedResponseException("{$label}: the Lettermint API answered with HTTP {$status} and a body that is not valid JSON.", $status, $text);
            }
        }
        if ($status < 400) {
            throw new UnexpectedResponseException("{$label}: the Lettermint API answered with an unexpected HTTP status {$status}.", $status, $text);
        }
        $body = null;
        if (trim($text) !== '') {
            try {
                $body = json_decode($text, true, 512, JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING);
            } catch (JsonException) {
                $contentType = trim(explode(';', $response->getHeaderLine('Content-Type'))[0]);
                throw new UnexpectedResponseException(
                    "{$label}: the Lettermint API answered with HTTP {$status} and a body that is not JSON".($contentType !== '' ? " ({$contentType})" : '').'.',
                    $status,
                    $text,
                );
            }
        }

        throw self::apiError($response, $body);
    }

    private static function apiError(ResponseInterface $response, mixed $body): ApiException
    {
        $status = $response->getStatusCode();
        $message = '';
        $code = null;
        $details = null;
        $errors = null;
        if (is_array($body)) {
            $error = $body['error'] ?? null;
            if (is_array($error)) {
                $code = is_string($error['code'] ?? null) ? $error['code'] : null;
                $message = is_string($error['message'] ?? null) ? $error['message'] : '';
                $details = $error['details'] ?? null;
            } elseif (is_string($error)) {
                $code = $error;
            }
            if ($message === '' && is_string($body['message'] ?? null)) {
                $message = $body['message'];
            }
            if (is_array($body['errors'] ?? null) && ($body['errors'] === [] || ! array_is_list($body['errors']))) {
                /** @var array<string, list<string>> $errors */
                $errors = $body['errors'];
            }
        }
        if ($message === '') {
            $message = $response->getReasonPhrase() !== '' ? $response->getReasonPhrase() : "HTTP {$status}";
        }

        return match (true) {
            $status === 401 => new AuthenticationException($status, $message, $code, $details, $body),
            $status === 403 => new PermissionException($status, $message, $code, $details, $body),
            $status === 404 => new NotFoundException($status, $message, $code, $details, $body),
            $status === 409 => new ConflictException($status, $message, $code, $details, $body),
            $status === 422 => new ValidationException($status, $message, $code, $details, $body, $errors),
            $status === 429 => new RateLimitException($status, $message, $code, $details, $body, self::retryAfter($response->getHeaderLine('Retry-After'))),
            $status >= 500 => new ServerException($status, $message, $code, $details, $body),
            default => new ApiException($status, $message, $code, $details, $body),
        };
    }

    /** Seconds from a `Retry-After` header: a number of seconds or an HTTP date. */
    private static function retryAfter(string $value): ?int
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        if (preg_match('/\A\d+\z/', $value) === 1) {
            return (int) $value;
        }
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }

        return max(0, (int) ceil($timestamp - microtime(true)));
    }

    /**
     * Guzzle 8 throws dedicated timeout exceptions; Guzzle 7 reports cURL error
     * 28 in the handler context, or "timed out" in the stream handler's message.
     */
    private function isTimeout(GuzzleException $exception, float $elapsed): bool
    {
        foreach (self::GUZZLE_TIMEOUT_EXCEPTIONS as $class) {
            if ($exception instanceof $class) {
                return true;
            }
        }
        $context = method_exists($exception, 'getHandlerContext') ? $exception->getHandlerContext() : [];
        $message = $exception->getMessage();

        return (is_array($context) && ($context['errno'] ?? null) === self::CURLE_OPERATION_TIMEDOUT)
            || stripos($message, 'timed out') !== false
            || stripos($message, 'timeout') !== false
            || $elapsed >= $this->timeout;
    }

    private static function version(): string
    {
        if (class_exists(InstalledVersions::class) && InstalledVersions::isInstalled('lettermint/lettermint-php')) {
            return InstalledVersions::getPrettyVersion('lettermint/lettermint-php') ?? 'unknown';
        }

        return 'unknown';
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return [
            'baseUrl' => $this->baseUrl,
            'timeout' => $this->timeout,
            'sendingToken' => $this->sendingToken === null ? null : '[redacted]',
            'teamToken' => $this->teamToken === null ? null : '[redacted]',
        ];
    }

    /**
     * @return never
     */
    public function __serialize(): array
    {
        throw new LettermintConfigException('A Lettermint client cannot be serialized, because it holds API tokens. Create it where you use it, for example from the service container.');
    }
}
