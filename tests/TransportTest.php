<?php

declare(strict_types=1);

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Lettermint\Exceptions\ApiException;
use Lettermint\Exceptions\AuthenticationException;
use Lettermint\Exceptions\ConflictException;
use Lettermint\Exceptions\ConnectionException;
use Lettermint\Exceptions\LettermintException;
use Lettermint\Exceptions\LettermintValidationException;
use Lettermint\Exceptions\NotFoundException;
use Lettermint\Exceptions\PermissionException;
use Lettermint\Exceptions\RateLimitException;
use Lettermint\Exceptions\RedirectException;
use Lettermint\Exceptions\ServerException;
use Lettermint\Exceptions\TimeoutException;
use Lettermint\Exceptions\UnexpectedResponseException;
use Lettermint\Exceptions\ValidationException;
use Lettermint\Tests\Support\FakeApi;
use Lettermint\Tests\Support\RecordedRequest;
use Lettermint\Types\SendMailResponse;

const MESSAGE = ['from' => 'a@example.test', 'to' => ['b@example.test'], 'subject' => 'x'];

/**
 * @return array{0: Throwable, 1: FakeApi}
 */
function sendError(callable $respond): array
{
    [$lettermint, $api] = client($respond);

    return [thrown(fn () => $lettermint->emails->send(MESSAGE)), $api];
}

describe('HTTP error mapping', function () {
    it('maps the status to an exception class', function (int $status, string $class) {
        $body = ['error' => ['code' => 'SOME_CODE', 'message' => 'Something went wrong.', 'details' => ['a' => 1]]];
        [$error, $api] = sendError(fn () => json($status, $body));
        expect($error)->toBeInstanceOf($class)
            ->and($error)->toBeInstanceOf(ApiException::class)
            ->and($error)->toBeInstanceOf(LettermintException::class)
            ->and($error->status)->toBe($status)
            ->and($error->getCode())->toBe($status)
            ->and($error->errorCode)->toBe('SOME_CODE')
            ->and($error->getMessage())->toBe('Something went wrong.')
            ->and($error->details)->toBe(['a' => 1])
            ->and($error->body)->toBe($body)
            ->and($api->requests)->toHaveCount(1);
        expectNoSecrets($error);
    })->with([
        [400, ApiException::class],
        [401, AuthenticationException::class],
        [403, PermissionException::class],
        [404, NotFoundException::class],
        [409, ConflictException::class],
        [410, ApiException::class],
        [422, ValidationException::class],
        [429, RateLimitException::class],
        [500, ServerException::class],
        [503, ServerException::class],
    ]);

    it('reads Laravel validation errors', function () {
        $body = ['message' => 'The to field is required.', 'errors' => ['to' => ['The to field is required.']]];
        [$error] = sendError(fn () => json(422, $body));
        expect($error)->toBeInstanceOf(ValidationException::class)
            ->and($error->status)->toBe(422)
            ->and($error->errorCode)->toBeNull()
            ->and($error->getMessage())->toBe('The to field is required.')
            ->and($error->errors)->toBe(['to' => ['The to field is required.']])
            ->and($error->body)->toBe($body);
    });

    it('keeps the legacy string error code', function () {
        [$error] = sendError(fn () => json(422, ['error' => 'DailyLimitExceeded', 'message' => 'Daily limit exceeded']));
        expect($error->errorCode)->toBe('DailyLimitExceeded')
            ->and($error->getMessage())->toBe('Daily limit exceeded');
    });

    it('keeps the Free-plan Sandbox 403 response', function () {
        $body = ['error' => ['code' => 'FEATURE_NOT_AVAILABLE', 'message' => 'Sandbox mode is available only on paid plans.']];
        [$error] = sendError(fn () => json(403, $body));
        expect($error)->toBeInstanceOf(PermissionException::class)
            ->and($error->errorCode)->toBe('FEATURE_NOT_AVAILABLE');
    });

    it('falls back to the reason phrase for an empty error body', function () {
        [$error] = sendError(fn () => text(404, '', [], 'Not Found'));
        expect($error)->toBeInstanceOf(NotFoundException::class)
            ->and($error->getMessage())->toBe('Not Found')
            ->and($error->body)->toBeNull();
        [$noReason] = sendError(fn () => new Response(599, [], ''));
        expect($noReason)->toBeInstanceOf(ServerException::class)
            ->and($noReason->getMessage())->toBe('HTTP 599');
    });

    it('reads Retry-After', function (string $header, ?int $expected) {
        [$error] = sendError(fn () => json(429, ['message' => 'Too Many Attempts.'], ['Retry-After' => $header]));
        expect($error)->toBeInstanceOf(RateLimitException::class)
            ->and($error->retryAfter)->toBe($expected);
    })->with([['120', 120], ['0', 0], ['soon', null]]);

    it('reads an HTTP-date Retry-After', function () {
        $date = gmdate('D, d M Y H:i:s \G\M\T', time() + 61);
        [$error] = sendError(fn () => json(429, ['message' => 'Slow down'], ['Retry-After' => $date]));
        expect($error->retryAfter)->toBeGreaterThanOrEqual(59)->toBeLessThanOrEqual(61);
    });

    it('reads Retry-After from a 5xx response', function () {
        [$error] = sendError(fn () => json(503, ['message' => 'Service Unavailable'], ['Retry-After' => '2']));
        expect($error)->toBeInstanceOf(ServerException::class)
            ->and($error->retryAfter)->toBe(2);
        [$plain] = sendError(fn () => json(500, ['message' => 'Server Error']));
        expect($plain)->toBeInstanceOf(ServerException::class)
            ->and($plain->retryAfter)->toBeNull();
    });

    it('does not retry', function () {
        [$error, $api] = sendError(fn () => json(503, ['message' => 'Down']));
        expect($error)->toBeInstanceOf(ServerException::class)
            ->and($api->requests)->toHaveCount(1);
    });
});

describe('unexpected responses', function () {
    it('raises a typed error with the status for an empty 2xx body', function () {
        [$error] = sendError(fn () => new Response(202, ['Content-Type' => 'application/json'], ''));
        expect($error)->toBeInstanceOf(UnexpectedResponseException::class)
            ->and($error->status)->toBe(202)
            ->and($error->getCode())->toBe(202)
            ->and($error->bodyExcerpt)->toBe('');
    });

    it('raises a typed error for an invalid JSON 2xx body', function () {
        [$error] = sendError(fn () => text(200, '{"message_id":'));
        expect($error)->toBeInstanceOf(UnexpectedResponseException::class)
            ->and($error->status)->toBe(200)
            ->and($error->bodyExcerpt)->toBe('{"message_id":');
    });

    it('raises a typed error for a JSON 2xx body that is not an object', function () {
        [$error] = sendError(fn () => json(202, ['a', 'b']));
        expect($error)->toBeInstanceOf(UnexpectedResponseException::class)
            ->and($error->getMessage())->toContain('not a JSON object');
    });

    it('raises a typed error with the status and an excerpt for an HTML 502', function () {
        $page = '<html><head><title>502 Bad Gateway</title></head><body>'.str_repeat('é', 300).'</body></html>';
        [$error] = sendError(fn () => text(502, $page, ['Content-Type' => 'text/html; charset=UTF-8']));
        expect($error)->toBeInstanceOf(UnexpectedResponseException::class)
            ->and($error->status)->toBe(502)
            ->and($error->getMessage())->toContain('HTTP 502')
            ->and($error->getMessage())->toContain('text/html')
            ->and($error->bodyExcerpt)->toStartWith('<html><head><title>502 Bad Gateway')
            ->and(strlen($error->bodyExcerpt))->toBeLessThanOrEqual(203)
            ->and(preg_match('//u', $error->bodyExcerpt))->toBe(1);
    });

    it('accepts unknown enum values and fields', function () {
        [$lettermint] = client(fn () => json(202, ['message_id' => 'm', 'status' => 'some_future_status', 'some_future_field' => ['nested' => [1]]]));
        $result = $lettermint->emails->send(MESSAGE);
        expect($result)->toBeInstanceOf(SendMailResponse::class)
            ->and($result->status)->toBe('some_future_status')
            ->and($result['some_future_field'])->toBe(['nested' => [1]])
            ->and($result->toArray())->toBe(['message_id' => 'm', 'status' => 'some_future_status', 'some_future_field' => ['nested' => [1]]]);
    });

    it('returns nothing for HTTP 204', function () {
        [$lettermint, $api] = client(fn () => new Response(204));
        expect($lettermint->projects->reportForwarding->delete('p'))->toBeNull()
            ->and($api->last()->method)->toBe('DELETE');
    });

    it('returns text endpoints as strings', function () {
        $source = "From: a@example.test\r\nSubject: x\r\n\r\nBody\r\n";
        [$lettermint] = client(fn () => text(200, $source, ['Content-Type' => 'message/rfc822']));
        expect($lettermint->messages->source('m'))->toBe($source)
            ->and($lettermint->messages->html('m'))->toBe($source)
            ->and($lettermint->messages->text('m'))->toBe($source);
    });

    it('rejects a request body that cannot be encoded as JSON before sending it', function () {
        [$lettermint, $api] = client();
        $error = thrown(fn () => $lettermint->emails->send([...MESSAGE, 'subject' => "\xB1\x31"]));
        expect($error)->toBeInstanceOf(LettermintValidationException::class)
            ->and($error->getMessage())->toContain('cannot be encoded as JSON')
            ->and($api->requests)->toBeEmpty();
    });
});

describe('redirects', function () {
    it('raises RedirectException and never follows it', function (int $status) {
        [$error, $api] = sendError(fn () => new Response($status, ['Location' => 'https://user:secret@attacker.example.test/v1/send?token=x'], '{"message":"Moved"}'));
        expect($error)->toBeInstanceOf(RedirectException::class)
            ->and($error->status)->toBe($status)
            ->and($error->getMessage())->not->toContain('attacker')
            ->and(json_encode($error))->not->toContain('attacker')
            ->and($api->requests)->toHaveCount(1);
    })->with([301, 302, 303, 307, 308]);

    it('applies to the Team API', function () {
        [$lettermint, $api] = client(fn () => new Response(307, ['Location' => '/elsewhere']));
        expect(fn () => $lettermint->ping())->toThrow(RedirectException::class)
            ->and($api->requests)->toHaveCount(1);
    });
});

describe('timeouts and network failures', function () {
    it('maps a cURL timeout to TimeoutException', function () {
        [$error] = sendError(fn (RecordedRequest $request) => throw new ConnectException(
            'cURL error 28: Operation timed out after 500 milliseconds',
            $request->request,
            null,
            ['errno' => 28],
        ));
        expect($error)->toBeInstanceOf(TimeoutException::class)
            ->and($error->timeout)->toBe(30.0)
            ->and($error->getMessage())->toBe('The request to the Lettermint API timed out after 30 seconds.')
            ->and($error->getPrevious())->toBeNull();
        expectNoSecrets($error);
    });

    it('wraps network failures in ConnectionException without the request', function () {
        [$error] = sendError(fn (RecordedRequest $request) => throw new ConnectException(
            'cURL error 7: Failed to connect to api.lettermint.co port 443',
            $request->request,
        ));
        expect($error)->toBeInstanceOf(ConnectionException::class)
            ->and($error->getMessage())->toBe('Could not reach the Lettermint API: cURL error 7: Failed to connect to api.lettermint.co port 443')
            ->and($error->getPrevious())->toBeNull();
        expectNoSecrets($error);
    });

    it('keeps the request (and its token) out of the exception trace', function () {
        $request = new Request('POST', BASE_URL.'/send', ['x-lettermint-token' => SENDING_TOKEN]);
        [$lettermint] = client(fn () => throw new ConnectException('Connection refused', $request));
        $error = thrown(fn () => $lettermint->emails->send(MESSAGE));
        ob_start();
        var_dump($error);
        $dump = (string) ob_get_clean();
        expect($dump)->not->toContain(SENDING_TOKEN);
    });
});
