<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\ServerRequest;
use Lettermint\Exceptions\LettermintConfigException;
use Lettermint\Exceptions\LettermintException;
use Lettermint\Exceptions\WebhookVerificationException;
use Lettermint\Webhook;
use Lettermint\WebhookPayload;

const NOW = 1700000000;
const SECRET = 'whsec_test-webhook-secret';
const BODY = '{"id":"d1","event":"message.delivered","timestamp":"2023-11-14T22:13:20Z","data":{"subject":"Hello 🌍"}}';

function sign(string $payload = BODY, int $timestamp = NOW, string $key = SECRET): string
{
    return "t={$timestamp},v1=".hash_hmac('sha256', "{$timestamp}.{$payload}", $key);
}

/**
 * @return array<string, string>
 */
function webhookHeaders(?string $signature = null, string|int $delivery = NOW): array
{
    return ['X-Lettermint-Signature' => $signature ?? sign(), 'X-Lettermint-Delivery' => (string) $delivery];
}

function webhook(string $secret = SECRET, int $tolerance = 300, int $now = NOW): Webhook
{
    return new Webhook($secret, $tolerance, fn (): int => $now);
}

function failure(callable $verify): WebhookVerificationException
{
    $error = thrown($verify);
    expect($error)->toBeInstanceOf(WebhookVerificationException::class);

    /** @var WebhookVerificationException $error */
    return $error;
}

describe('Webhook', function () {
    it('verifies headers and returns the payload', function () {
        $payload = webhook()->verify(BODY, webhookHeaders());
        expect($payload)->toBeInstanceOf(WebhookPayload::class)
            ->and($payload->event)->toBe('message.delivered')
            ->and($payload->id)->toBe('d1')
            ->and($payload->timestamp)->toBe('2023-11-14T22:13:20Z')
            ->and($payload->data)->toBe(['subject' => 'Hello 🌍'])
            ->and($payload['data'])->toBe(['subject' => 'Hello 🌍'])
            ->and($payload->toArray())->toBe(json_decode(BODY, true))
            ->and(new WebhookVerificationException('signature_mismatch', 'x'))->toBeInstanceOf(LettermintException::class);
    });

    it('accepts a PSR-7 request and a header bag', function () {
        $request = new ServerRequest('POST', 'https://example.test/hook', webhookHeaders(), BODY);
        expect(webhook()->verify((string) $request->getBody(), $request)->event)->toBe('message.delivered');
        $bag = new class(['x-lettermint-signature' => [sign()], 'x-lettermint-delivery' => [(string) NOW]])
        {
            /** @param array<string, list<string|null>> $headers */
            public function __construct(private array $headers) {}

            /** @return array<string, list<string|null>> */
            public function all(): array
            {
                return $this->headers;
            }
        };
        expect(webhook()->verify(BODY, $bag)->event)->toBe('message.delivered')
            ->and(webhook()->verify(BODY, new ArrayIterator(webhookHeaders()))->event)->toBe('message.delivered');
    });

    it('rejects an unsupported header container', function () {
        expect(fn () => webhook()->verify(BODY, new stdClass))->toThrow(LettermintConfigException::class);
    });

    it('verifies the exact bytes', function () {
        $raw = ' '.BODY."\n";
        expect(webhook()->verify($raw, webhookHeaders(sign($raw)))->event)->toBe('message.delivered')
            ->and(failure(fn () => webhook()->verify(BODY, webhookHeaders(sign($raw))))->reason)->toBe('signature_mismatch');
    });

    it('accepts timestamps within the tolerance', function (int $offset) {
        $t = NOW + $offset;
        expect(webhook()->verify(BODY, webhookHeaders(sign(BODY, $t), $t))->event)->toBe('message.delivered');
    })->with([-300, 0, 300]);

    it('rejects timestamps outside the tolerance', function (int $offset) {
        $t = NOW + $offset;
        expect(failure(fn () => webhook()->verify(BODY, webhookHeaders(sign(BODY, $t), $t)))->reason)->toBe('timestamp_out_of_tolerance');
    })->with([-301, 301]);

    it('uses a custom tolerance and keeps the check enabled at zero', function () {
        failure(fn () => webhook(tolerance: 60)->verify(BODY, webhookHeaders(sign(BODY, NOW - 61), NOW - 61)));
        $webhook = webhook(tolerance: 0);
        expect($webhook->tolerance)->toBe(0)
            ->and($webhook->verify(BODY, webhookHeaders())->event)->toBe('message.delivered');
        failure(fn () => $webhook->verify(BODY, webhookHeaders(sign(BODY, NOW - 1), NOW - 1)));
    });

    it('uses the system clock by default', function () {
        $t = time();
        expect((new Webhook(SECRET))->verify(BODY, webhookHeaders(sign(BODY, $t), $t))->event)->toBe('message.delivered');
    });

    it('rejects an invalid configuration', function () {
        expect(fn () => new Webhook(''))->toThrow(LettermintConfigException::class, 'signing secret')
            ->and(fn () => new Webhook(SECRET, -1))->toThrow(LettermintConfigException::class, 'tolerance');
    });

    it('rejects a changed payload or a wrong secret', function () {
        expect(failure(fn () => webhook()->verify('{}', webhookHeaders()))->reason)->toBe('signature_mismatch')
            ->and(failure(fn () => webhook('wrong')->verify(BODY, webhookHeaders()))->reason)->toBe('signature_mismatch');
    });

    it('rejects malformed signature headers', function (string $signature, string $reason) {
        expect(failure(fn () => webhook()->verifySignature(BODY, $signature))->reason)->toBe($reason);
    })->with([
        ['', 'signature_header_missing'],
        ['v1=abc', 'signature_header_malformed'],
        ['t='.NOW, 'signature_header_malformed'],
        ['t='.NOW.',v1=abc', 'signature_header_malformed'],
        ['t='.NOW.',v1='.str_repeat('g', 64), 'signature_header_malformed'],
        ['t='.NOW.',v1='.str_repeat('a', 63), 'signature_header_malformed'],
        ['t='.NOW.',v1='.str_repeat('a', 65), 'signature_header_malformed'],
        ['t='.NOW.','.sign(), 'signature_header_malformed'],
        [str_replace('t='.NOW, 't=NaN', sign()), 'signature_header_malformed'],
        [str_replace('t='.NOW, 't=1e9', sign()), 'signature_header_malformed'],
        [str_replace('t='.NOW, 't=-1', sign()), 'signature_header_malformed'],
        [str_replace('t='.NOW, 't=9007199254740992', sign()), 'signature_header_malformed'],
        [str_replace('t='.NOW, 't=99999999999999999999', sign()), 'signature_header_malformed'],
        [str_replace('t='.NOW, 't='.NOW.'=extra', sign()), 'signature_header_malformed'],
        [sign().'=extra', 'signature_header_malformed'],
        [sign().'é', 'signature_header_malformed'],
        [sign()."\xff", 'signature_header_malformed'],
    ]);

    it('accepts any matching v1 signature and ignores unsupported versions', function () {
        $signature = 'v2=ignored, v1='.str_repeat('0', 64).', '.sign().', v1=malformed';
        $uppercase = 't='.NOW.',v1='.strtoupper(hash_hmac('sha256', NOW.'.'.BODY, SECRET));
        expect(webhook()->verifySignature(BODY, $signature)->event)->toBe('message.delivered')
            ->and(webhook()->verifySignature(BODY, $uppercase)->event)->toBe('message.delivered');
    });

    it('rejects empty and non-object bodies', function () {
        expect(failure(fn () => webhook()->verifySignature('', sign('')))->reason)->toBe('body_invalid')
            ->and(failure(fn () => webhook()->verifySignature('invalid', sign('invalid')))->reason)->toBe('payload_invalid')
            ->and(failure(fn () => webhook()->verifySignature('[1]', sign('[1]')))->reason)->toBe('payload_invalid')
            ->and(failure(fn () => webhook()->verifySignature('{"data":{}}', sign('{"data":{}}')))->reason)->toBe('payload_invalid')
            ->and(failure(fn () => webhook()->verifySignature('invalid', sign()))->reason)->toBe('signature_mismatch');
    });

    it('checks the optional delivery timestamp of verifySignature', function () {
        expect(webhook()->verifySignature(BODY, sign(), NOW)->event)->toBe('message.delivered')
            ->and(webhook()->verifySignature(BODY, sign(), (string) NOW)->event)->toBe('message.delivered');
        foreach ([NOW + 1, 'NaN', ''] as $timestamp) {
            expect(failure(fn () => webhook()->verifySignature(BODY, sign(), $timestamp))->reason)->toBe('delivery_timestamp_mismatch');
        }
    });

    it('reads headers case-insensitively, also as lists', function () {
        expect(webhook()->verify(BODY, [
            'x-lettermint-signature' => sign(),
            'X-LETTERMINT-DELIVERY' => [(string) NOW],
            'host' => 'localhost',
        ])->event)->toBe('message.delivered');
    });

    it('rejects missing, ambiguous or invalid headers', function (array $headers, string $reason) {
        $error = failure(fn () => webhook()->verify(BODY, $headers));
        expect($error->reason)->toBe($reason);
    })->with([
        [[], 'signature_header_missing'],
        [['x-lettermint-signature' => sign()], 'delivery_header_missing'],
        [['x-lettermint-delivery' => (string) NOW], 'signature_header_missing'],
        [['x-lettermint-signature' => sign(), 'x-lettermint-delivery' => null], 'delivery_header_missing'],
        [['x-lettermint-signature' => [sign(), sign()], 'x-lettermint-delivery' => (string) NOW], 'signature_header_malformed'],
        [['x-lettermint-signature' => sign(), 'x-lettermint-delivery' => ''], 'delivery_timestamp_mismatch'],
        [['x-lettermint-signature' => sign(), 'x-lettermint-delivery' => NOW.'junk'], 'delivery_timestamp_mismatch'],
        [['x-lettermint-signature' => sign(), 'x-lettermint-delivery' => (string) (NOW + 1)], 'delivery_timestamp_mismatch'],
        [['x-lettermint-signature' => sign(), 'X-Lettermint-Signature' => sign(), 'x-lettermint-delivery' => (string) NOW], 'signature_header_malformed'],
        [['x-lettermint-signature' => sign(), 'x-lettermint-delivery' => [(string) NOW, (string) NOW]], 'delivery_timestamp_mismatch'],
        [['x-lettermint-signature' => ['nested' => ['x']], 'x-lettermint-delivery' => (string) NOW], 'signature_header_malformed'],
    ]);

    it('never shows the secret', function () {
        $webhook = webhook();
        $error = failure(fn () => $webhook->verify('{}', webhookHeaders('t=1,v1='.str_repeat('0', 64), 1)));
        expectNoSecrets($webhook, SECRET);
        expectNoSecrets($error, SECRET);
        expect(fn () => serialize($webhook))->toThrow(LettermintConfigException::class);
        ob_start();
        var_dump($webhook);
        expect((string) ob_get_clean())->toContain('tolerance');
        $construct = thrown(fn () => new Webhook(SECRET, -1));
        expectNoSecrets($construct, SECRET);
    });
});

describe('conformance webhook vectors', function () {
    /** @var array{vectors: list<array<string, mixed>>} $fixture */
    $fixture = json_decode((string) file_get_contents(__DIR__.'/fixtures/webhooks.json'), true, 512, JSON_THROW_ON_ERROR);

    it('uses the full vector set', function () use ($fixture) {
        expect(count($fixture['vectors']))->toBeGreaterThanOrEqual(25);
    });

    it('verifies the vector', function (array $vector) {
        $webhook = new Webhook($vector['secret'], $vector['tolerance'], fn (): int => $vector['now']);
        $body = (string) base64_decode($vector['body_base64'], true);
        if ($vector['expect'] === 'valid') {
            expect($webhook->verify($body, $vector['headers'])->toArray())->toBe(json_decode($body, true));
        } else {
            expect(failure(fn () => $webhook->verify($body, $vector['headers']))->reason)->toBe($vector['reason']);
        }
    })->with(array_combine(array_column($fixture['vectors'], 'id'), array_map(fn (array $vector): array => [$vector], $fixture['vectors'])));
});
