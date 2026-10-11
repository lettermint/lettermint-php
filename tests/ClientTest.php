<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Lettermint\Exceptions\LettermintConfigException;
use Lettermint\Exceptions\LettermintException;
use Lettermint\Lettermint;
use Lettermint\Resources\Emails;
use Lettermint\Tests\Support\FakeApi;

const TEAM = 'lm_team_A1b2C3d4E5A1b2C3d4E5A1b2C3d4E5A1b2C3d4E5';
const PROJECT_32 = 'lm_Z9y8X7w6V5u4T3s2Z9y8X7w6V5u4T3s2';
const PROJECT_22 = 'lm_Q1w2E3r4T5y6U7i8O9p0A1';

describe('construction', function () {
    it('requires at least one token', function () {
        $error = thrown(fn () => new Lettermint);
        expect($error)->toBeInstanceOf(LettermintConfigException::class)
            ->and($error)->toBeInstanceOf(LettermintException::class)
            ->and($error->getMessage())->toBe('Pass sendingToken, teamToken or both.');
    });

    it('rejects invalid tokens without echoing them', function (array $options, string $message) {
        $error = thrown(fn () => new Lettermint(...$options));
        expect($error)->toBeInstanceOf(LettermintConfigException::class)
            ->and($error->getMessage())->toContain($message)
            ->and($error->getMessage())->not->toContain(SENDING_TOKEN)
            ->and($error->getMessage())->not->toContain(TEAM_TOKEN);
        expectNoSecrets($error);
    })->with([
        'empty sending token' => [['sendingToken' => ''], 'sendingToken must be a non-empty string.'],
        'empty team token' => [['teamToken' => ''], 'teamToken must be a non-empty string.'],
        'newline' => [['sendingToken' => SENDING_TOKEN."\n"], 'sendingToken contains whitespace'],
        'Bearer prefix' => [['teamToken' => 'Bearer '.TEAM_TOKEN], 'teamToken contains whitespace'],
        'token and explicit token' => [['token' => SENDING_TOKEN, 'teamToken' => TEAM_TOKEN], 'not both'],
    ]);

    it('rejects an invalid base URL', function (string $baseUrl, string $message) {
        expect(fn () => new Lettermint(sendingToken: SENDING_TOKEN, baseUrl: $baseUrl))
            ->toThrow(LettermintConfigException::class, $message);
    })->with([
        ['not a url', 'absolute http(s) URL'],
        ['ftp://api.example.test', 'absolute http(s) URL'],
        ['https://user:pass@api.example.test/v1', 'must not contain credentials'],
        ['https://api.example.test/v1?x=1', 'must not contain credentials'],
        ['https://api.example.test/v1#x', 'must not contain credentials'],
        ["https://api.example.test/v1\n", 'whitespace'],
    ]);

    it('rejects a timeout that is not positive', function (float $timeout) {
        expect(fn () => new Lettermint(sendingToken: SENDING_TOKEN, timeout: $timeout))
            ->toThrow(LettermintConfigException::class, 'timeout must be a positive number of seconds.');
    })->with([0.0, -1.0, NAN, INF]);

    it('keeps the base URL path and strips trailing slashes', function () {
        [$lettermint, $api] = client(fn () => text(200, 'pong'), ['baseUrl' => 'https://custom.example.test/api/v1///']);
        $lettermint->ping();
        expect($api->last()->url)->toBe('https://custom.example.test/api/v1/ping');
    });

    it('sends the timeout and disables redirects on every request', function () {
        [$lettermint, $api] = client(fn () => text(200, 'pong'), ['timeout' => 7]);
        $lettermint->ping();
        expect($api->last()->options)->toMatchArray([
            'allow_redirects' => false,
            'http_errors' => false,
            'timeout' => 7.0,
            'connect_timeout' => 7.0,
        ]);
    });

    it('sends a User-Agent and Accept header', function () {
        [$lettermint, $api] = client(fn () => text(200, 'pong'));
        $lettermint->ping();
        expect($api->last()->headers['user-agent'])->toMatch('/^lettermint-php\/\S+ \(PHP \d+\.\d+\.\d+/')
            ->and($api->last()->headers['accept'])->toBe('application/json');
    });

    it('works without an injected HTTP client', function () {
        $lettermint = new Lettermint(sendingToken: SENDING_TOKEN);
        expect($lettermint->emails)->toBeInstanceOf(Emails::class);
    });
});

describe('token shorthand', function () {
    $headers = function (string $token): array {
        $api = new FakeApi(fn () => text(200, 'pong'));
        $lettermint = new Lettermint($token, httpClient: $api->client());
        $lettermint->ping();

        return $api->last()->headers;
    };

    it('detects a team token first', function () use ($headers) {
        $sent = $headers(TEAM);
        expect($sent['authorization'])->toBe('Bearer '.TEAM)
            ->and($sent)->not->toHaveKey('x-lettermint-token');
    });

    it('detects project sending tokens', function (string $token) use ($headers) {
        $sent = $headers($token);
        expect($sent['x-lettermint-token'])->toBe($token)
            ->and($sent)->not->toHaveKey('authorization');
    })->with(['32 characters' => PROJECT_32, '22 characters' => PROJECT_22]);

    it('rejects other formats without echoing them', function (string $token) {
        $error = thrown(fn () => new Lettermint($token));
        expect($error)->toBeInstanceOf(LettermintConfigException::class)
            ->and($error->getMessage())->toBe('Unrecognised token format; pass sendingToken or teamToken instead.');
        if (strlen($token) > 8) {
            expectNoSecrets($error, $token);
        }
    })->with([
        'an SSO token' => 'lm_sso_'.str_repeat('a1B2c3D4', 4),
        'an empty string' => '',
        'a JWT' => 'eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiIxIn0.c2lnbmF0dXJl',
        'an unknown prefix' => 'sk_live_0123456789abcdef',
        'a bare team prefix' => 'lm_team_',
        'a bare project prefix' => 'lm_',
        'a trailing newline' => PROJECT_32."\n",
        'a dash' => 'lm_abc-def',
    ]);
});

describe('single-token clients', function () {
    it('rejects Team API calls without a team token, naming the option', function () {
        $api = new FakeApi;
        $lettermint = new Lettermint(sendingToken: SENDING_TOKEN, httpClient: $api->client());
        $calls = [
            fn () => $lettermint->domains->list(),
            fn () => $lettermint->projects->reportForwarding->retrieve('project_1'),
            fn () => $lettermint->team->members->list(),
            fn () => $lettermint->webhooks->deliveries->list('webhook_1'),
            fn () => $lettermint->analytics(['metrics' => ['accepted']]),
            fn () => $lettermint->analyticsPages(['metrics' => ['accepted']]),
            fn () => $lettermint->blockedFileTypes(),
            fn () => $lettermint->messages->process('message_1'),
            fn () => $lettermint->domains->iterate(),
        ];
        foreach ($calls as $call) {
            $error = thrown($call);
            expect($error)->toBeInstanceOf(LettermintConfigException::class)
                ->and($error->getMessage())->toMatch('/^[\w.]+ needs teamToken; pass it as new Lettermint\(teamToken: \.\.\.\)\.$/');
        }
        expect($api->requests)->toBeEmpty();
    });

    it('rejects sending without a sending token and never falls back to the team token', function () {
        $api = new FakeApi;
        $lettermint = new Lettermint(teamToken: TEAM_TOKEN, httpClient: $api->client());
        $message = ['from' => 'a@example.test', 'to' => ['b@example.test'], 'subject' => 'Hi'];
        expect(fn () => $lettermint->emails->send($message))->toThrow(LettermintConfigException::class, 'emails.send needs sendingToken')
            ->and(fn () => $lettermint->emails->sendBatch([$message]))->toThrow(LettermintConfigException::class, 'emails.sendBatch needs sendingToken')
            ->and(fn () => $lettermint->emails->ping())->toThrow(LettermintConfigException::class, 'emails.ping needs sendingToken')
            ->and(fn () => $lettermint->emails->compose())->toThrow(LettermintConfigException::class, 'emails.compose needs sendingToken')
            ->and($api->requests)->toBeEmpty();
    });

    it('pings with the team token when present, otherwise the sending token', function () {
        [$both, $bothApi] = client(fn () => text(200, "pong\n"));
        expect($both->ping())->toBe('pong')
            ->and($bothApi->last()->headers['authorization'])->toBe('Bearer '.TEAM_TOKEN)
            ->and($bothApi->last()->headers)->not->toHaveKey('x-lettermint-token');

        [$sendingOnly, $sendingApi] = client(fn () => text(200, 'pong'), ['teamToken' => null]);
        $sendingOnly->ping();
        expect($sendingApi->last()->headers['x-lettermint-token'])->toBe(SENDING_TOKEN)
            ->and($sendingApi->last()->headers)->not->toHaveKey('authorization');

        $both->emails->ping();
        expect($bothApi->last()->headers['x-lettermint-token'])->toBe(SENDING_TOKEN)
            ->and($bothApi->last()->headers)->not->toHaveKey('authorization');
    });

    it('lets reschedule and cancel use the sending token when no team token is set', function () {
        $scheduled = ['message_id' => 'm', 'status' => 'scheduled', 'scheduled_at' => null];
        [$sendingOnly, $api] = client(fn () => json(200, $scheduled), ['teamToken' => null]);
        $sendingOnly->messages->cancel('m');
        $sendingOnly->messages->reschedule('m', ['scheduled_at' => '2026-10-05T09:00:00Z']);
        foreach ($api->requests as $request) {
            expect($request->headers['x-lettermint-token'])->toBe(SENDING_TOKEN)
                ->and($request->headers)->not->toHaveKey('authorization');
        }
        [$both, $bothApi] = client(fn () => json(200, $scheduled));
        $both->messages->cancel('m');
        expect($bothApi->last()->headers['authorization'])->toBe('Bearer '.TEAM_TOKEN);
    });
});

describe('redaction', function () {
    it('never shows tokens on the client, any sub-client or a builder', function () {
        [$lettermint] = client();
        $subjects = [
            $lettermint,
            $lettermint->emails,
            $lettermint->domains,
            $lettermint->messages,
            $lettermint->projects,
            $lettermint->projects->reportForwarding,
            $lettermint->routes,
            $lettermint->stats,
            $lettermint->suppressions,
            $lettermint->team,
            $lettermint->team->members,
            $lettermint->webhooks,
            $lettermint->webhooks->deliveries,
            $lettermint->emails->compose()->from('a@example.test')->to('b@example.test')->subject('Hi'),
        ];
        foreach ($subjects as $subject) {
            expectNoSecrets($subject);
        }
    });

    it('shows which tokens are configured', function () {
        [$lettermint] = client(null, ['teamToken' => null]);
        expect($lettermint->jsonSerialize())->toBe([
            'baseUrl' => BASE_URL,
            'timeout' => 30.0,
            'sendingToken' => '[redacted]',
            'teamToken' => null,
        ]);
        ob_start();
        var_dump($lettermint);
        expect((string) ob_get_clean())->toContain('[redacted]');
    });

    it('refuses to serialize objects that hold a token', function () {
        [$lettermint] = client();
        foreach ([$lettermint, $lettermint->domains, $lettermint->emails->compose()] as $subject) {
            expect(fn () => serialize($subject))->toThrow(LettermintConfigException::class, 'cannot be serialized');
        }
    });

    it('keeps tokens out of exception traces when a constructor argument is rejected', function () {
        $error = thrown(fn () => new Lettermint(sendingToken: SENDING_TOKEN, teamToken: 'Bearer '.TEAM_TOKEN));
        expectNoSecrets($error);
    });
});

it('has no mutable state between requests', function () {
    [$lettermint, $api] = client(fn () => new Response(202, ['Content-Type' => 'application/json'], '{"message_id":"m","status":"pending"}'));
    $lettermint->emails->send(['from' => 'a@example.test', 'to' => ['b@example.test'], 'subject' => 'One'], 'key-1');
    $lettermint->emails->send(['from' => 'a@example.test', 'to' => ['c@example.test'], 'subject' => 'Two']);
    expect($api->requests[0]->headers['idempotency-key'])->toBe('key-1')
        ->and($api->requests[1]->headers)->not->toHaveKey('idempotency-key')
        ->and($api->requests[1]->json())->toBe(['from' => 'a@example.test', 'to' => ['c@example.test'], 'subject' => 'Two']);
});
