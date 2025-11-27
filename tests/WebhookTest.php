<?php

use Lettermint\Exceptions\InvalidSignatureException;
use Lettermint\Exceptions\JsonDecodeException;
use Lettermint\Exceptions\TimestampToleranceException;
use Lettermint\Exceptions\WebhookVerificationException;
use Lettermint\Webhook;

const TEST_SECRET = 'test_secret_key';
const TEST_PAYLOAD = '{"event":"email.sent","data":{"id":"123"}}';

function generateValidSignature(string $payload, int $timestamp, string $secret = TEST_SECRET): string
{
    $signedContent = $timestamp.'.'.$payload;
    $signature = hash_hmac('sha256', $signedContent, $secret);

    return "t={$timestamp},v1={$signature}";
}

test('it verifies valid webhook signature', function () {
    $webhook = new Webhook(TEST_SECRET);
    $timestamp = time();
    $signature = generateValidSignature(TEST_PAYLOAD, $timestamp);

    $result = $webhook->verify(TEST_PAYLOAD, $signature, $timestamp);

    expect($result)->toBeArray()
        ->and($result['event'])->toBe('email.sent')
        ->and($result['data']['id'])->toBe('123');
});

test('it verifies signature without explicit timestamp parameter', function () {
    $webhook = new Webhook(TEST_SECRET);
    $timestamp = time();
    $signature = generateValidSignature(TEST_PAYLOAD, $timestamp);

    $result = $webhook->verify(TEST_PAYLOAD, $signature);

    expect($result)->toBeArray()
        ->and($result['event'])->toBe('email.sent');
});

test('it rejects invalid signature', function () {
    $webhook = new Webhook(TEST_SECRET);
    $timestamp = time();
    $invalidSignature = "t={$timestamp},v1=invalid_signature_hash";

    $webhook->verify(TEST_PAYLOAD, $invalidSignature, $timestamp);
})->throws(InvalidSignatureException::class, 'Signature verification failed');

test('it rejects signature with wrong secret', function () {
    $webhook = new Webhook(TEST_SECRET);
    $timestamp = time();
    $signature = generateValidSignature(TEST_PAYLOAD, $timestamp, 'wrong_secret');

    $webhook->verify(TEST_PAYLOAD, $signature, $timestamp);
})->throws(InvalidSignatureException::class);

test('it rejects signature with modified payload', function () {
    $webhook = new Webhook(TEST_SECRET);
    $timestamp = time();
    $signature = generateValidSignature(TEST_PAYLOAD, $timestamp);
    $modifiedPayload = '{"event":"email.sent","data":{"id":"456"}}';

    $webhook->verify($modifiedPayload, $signature, $timestamp);
})->throws(InvalidSignatureException::class);

test('it rejects timestamp outside tolerance window', function () {
    $webhook = new Webhook(TEST_SECRET, 300);
    $oldTimestamp = time() - 400;
    $signature = generateValidSignature(TEST_PAYLOAD, $oldTimestamp);

    $webhook->verify(TEST_PAYLOAD, $signature, $oldTimestamp);
})->throws(TimestampToleranceException::class);

test('it accepts timestamp within tolerance window', function () {
    $webhook = new Webhook(TEST_SECRET, 300);
    $recentTimestamp = time() - 200;
    $signature = generateValidSignature(TEST_PAYLOAD, $recentTimestamp);

    $result = $webhook->verify(TEST_PAYLOAD, $signature, $recentTimestamp);

    expect($result)->toBeArray();
});

test('it allows custom tolerance configuration', function () {
    $webhook = new Webhook(TEST_SECRET, 600);
    $timestamp = time() - 500;
    $signature = generateValidSignature(TEST_PAYLOAD, $timestamp);

    $result = $webhook->verify(TEST_PAYLOAD, $signature, $timestamp);

    expect($result)->toBeArray();
});

test('it rejects malformed signature format', function () {
    $webhook = new Webhook(TEST_SECRET);
    $malformedSignature = 'invalid_format';

    $webhook->verify(TEST_PAYLOAD, $malformedSignature);
})->throws(WebhookVerificationException::class, 'Invalid signature format');

test('it rejects signature missing timestamp', function () {
    $webhook = new Webhook(TEST_SECRET);
    $signature = 'v1=somehash';

    $webhook->verify(TEST_PAYLOAD, $signature);
})->throws(WebhookVerificationException::class, 'Invalid signature format');

test('it rejects signature missing hash', function () {
    $webhook = new Webhook(TEST_SECRET);
    $timestamp = time();
    $signature = "t={$timestamp}";

    $webhook->verify(TEST_PAYLOAD, $signature);
})->throws(WebhookVerificationException::class, 'Invalid signature format');

test('it verifies using static method', function () {
    $timestamp = time();
    $signature = generateValidSignature(TEST_PAYLOAD, $timestamp);

    $result = Webhook::verifySignature(
        TEST_PAYLOAD,
        $signature,
        TEST_SECRET,
        $timestamp
    );

    expect($result)->toBeArray()
        ->and($result['event'])->toBe('email.sent');
});

test('it verifies using static method with custom tolerance', function () {
    $timestamp = time() - 500;
    $signature = generateValidSignature(TEST_PAYLOAD, $timestamp);

    $result = Webhook::verifySignature(
        TEST_PAYLOAD,
        $signature,
        TEST_SECRET,
        $timestamp,
        600
    );

    expect($result)->toBeArray();
});

test('it verifies from headers array', function () {
    $webhook = new Webhook(TEST_SECRET);
    $timestamp = time();
    $signature = generateValidSignature(TEST_PAYLOAD, $timestamp);

    $headers = [
        'X-Lettermint-Signature' => $signature,
        'X-Lettermint-Delivery' => (string) $timestamp,
    ];

    $result = $webhook->verifyHeaders($headers, TEST_PAYLOAD);

    expect($result)->toBeArray()
        ->and($result['event'])->toBe('email.sent');
});

test('it normalizes header names case-insensitively', function () {
    $webhook = new Webhook(TEST_SECRET);
    $timestamp = time();
    $signature = generateValidSignature(TEST_PAYLOAD, $timestamp);

    $headers = [
        'x-lettermint-signature' => $signature,
        'x-lettermint-delivery' => (string) $timestamp,
    ];

    $result = $webhook->verifyHeaders($headers, TEST_PAYLOAD);

    expect($result)->toBeArray();
});

test('it rejects when signature header is missing', function () {
    $webhook = new Webhook(TEST_SECRET);
    $headers = [
        'X-Lettermint-Delivery' => (string) time(),
    ];

    $webhook->verifyHeaders($headers, TEST_PAYLOAD);
})->throws(WebhookVerificationException::class, 'Missing signature header');

test('it rejects when delivery header is missing', function () {
    $webhook = new Webhook(TEST_SECRET);
    $timestamp = time();
    $signature = generateValidSignature(TEST_PAYLOAD, $timestamp);

    $headers = [
        'X-Lettermint-Signature' => $signature,
    ];

    $webhook->verifyHeaders($headers, TEST_PAYLOAD);
})->throws(WebhookVerificationException::class, 'Missing delivery header');

test('it rejects timestamp mismatch between signature and delivery headers', function () {
    $webhook = new Webhook(TEST_SECRET);
    $timestamp1 = time();
    $timestamp2 = time() - 10;
    $signature = generateValidSignature(TEST_PAYLOAD, $timestamp1);

    $webhook->verify(TEST_PAYLOAD, $signature, $timestamp2);
})->throws(WebhookVerificationException::class, 'Timestamp mismatch');

test('it handles future timestamps within tolerance', function () {
    $webhook = new Webhook(TEST_SECRET, 300);
    $futureTimestamp = time() + 100;
    $signature = generateValidSignature(TEST_PAYLOAD, $futureTimestamp);

    $result = $webhook->verify(TEST_PAYLOAD, $signature, $futureTimestamp);

    expect($result)->toBeArray();
});

test('it rejects future timestamps outside tolerance', function () {
    $webhook = new Webhook(TEST_SECRET, 300);
    $futureTimestamp = time() + 400;
    $signature = generateValidSignature(TEST_PAYLOAD, $futureTimestamp);

    $webhook->verify(TEST_PAYLOAD, $signature, $futureTimestamp);
})->throws(TimestampToleranceException::class);

test('it rejects empty secret', function () {
    new Webhook('');
})->throws(InvalidArgumentException::class, 'Webhook secret cannot be empty');

test('it rejects invalid JSON payload', function () {
    $webhook = new Webhook(TEST_SECRET);
    $invalidJson = 'not valid json';
    $timestamp = time();
    $signedContent = $timestamp.'.'.$invalidJson;
    $signatureHash = hash_hmac('sha256', $signedContent, TEST_SECRET);
    $signature = "t={$timestamp},v1={$signatureHash}";

    $webhook->verify($invalidJson, $signature, $timestamp);
})->throws(JsonDecodeException::class, 'Failed to decode webhook payload');
