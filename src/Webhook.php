<?php

declare(strict_types=1);

namespace Lettermint;

use Closure;
use JsonException;
use Lettermint\Exceptions\LettermintConfigException;
use Lettermint\Exceptions\WebhookVerificationException;
use Psr\Http\Message\MessageInterface;
use SensitiveParameterValue;
use Traversable;

/**
 * Verifies Lettermint webhook deliveries: an HMAC-SHA256 signature over
 * `"<t>." + raw body`, keyed with the endpoint's signing secret (`whsec_…`,
 * used as it is), compared in constant time.
 *
 * ```php
 * $webhook = new Webhook(getenv('LETTERMINT_WEBHOOK_SECRET'));
 * $payload = $webhook->verify($request->getContent(), $request->headers);
 * ```
 */
final class Webhook
{
    /** The default timestamp tolerance in seconds. */
    public const DEFAULT_TOLERANCE = 300;

    public const SIGNATURE_HEADER = 'X-Lettermint-Signature';

    public const DELIVERY_HEADER = 'X-Lettermint-Delivery';

    private const PRINTABLE_ASCII = '/\A[\x20-\x7e]*\z/';

    private const DIGITS = '/\A[0-9]+\z/';

    private const HEX_SHA256 = '/\A[0-9a-fA-F]{64}\z/';

    /** The largest timestamp accepted, as in the other Lettermint SDKs (2^53 - 1). */
    private const MAX_TIMESTAMP = 9007199254740991;

    private readonly SensitiveParameterValue $secret;

    /** @var Closure(): int */
    private readonly Closure $clock;

    /**
     * @param  string  $secret  The webhook's signing secret, including its `whsec_` prefix.
     * @param  int  $tolerance  Maximum difference between the signed timestamp and now, in seconds, in either direction. `0` accepts only the current second.
     * @param  (Closure(): int)|null  $clock  Returns the current Unix time in seconds. For tests; default `time()`.
     *
     * @throws LettermintConfigException When the secret is empty or the tolerance is negative.
     */
    public function __construct(
        #[\SensitiveParameter] string $secret,
        public readonly int $tolerance = self::DEFAULT_TOLERANCE,
        ?Closure $clock = null,
    ) {
        if ($secret === '') {
            throw new LettermintConfigException('The webhook signing secret must be a non-empty string.');
        }
        if ($tolerance < 0) {
            throw new LettermintConfigException('tolerance must be a non-negative number of seconds.');
        }
        $this->secret = new SensitiveParameterValue($secret);
        $this->clock = $clock ?? static fn (): int => time();
    }

    /**
     * Verifies a delivery from its raw body and request headers and returns
     * the decoded payload. Requires `X-Lettermint-Signature` and
     * `X-Lettermint-Delivery` (which must equal the signed timestamp). Header
     * names are case-insensitive.
     *
     * @param  string  $rawBody  The request body exactly as received. Do not decode and re-encode it.
     * @param  array<array-key, mixed>|object  $headers  A header array (`['X-Lettermint-Signature' => '…']` or lists of values, as from Laravel/Symfony `$request->headers->all()`), a Symfony HeaderBag, or a PSR-7 message.
     *
     * @throws WebhookVerificationException When the delivery is not genuine.
     */
    public function verify(string $rawBody, array|object $headers): WebhookPayload
    {
        $headers = self::headerList($headers);
        [$signatureState, $signature] = self::header($headers, strtolower(self::SIGNATURE_HEADER));
        if ($signatureState === 'missing') {
            throw new WebhookVerificationException(WebhookVerificationException::SIGNATURE_HEADER_MISSING, 'The X-Lettermint-Signature header is missing.');
        }
        if ($signatureState === 'ambiguous') {
            throw new WebhookVerificationException(WebhookVerificationException::SIGNATURE_HEADER_MALFORMED, 'The request has more than one X-Lettermint-Signature header.');
        }
        [$deliveryState, $delivery] = self::header($headers, strtolower(self::DELIVERY_HEADER));
        if ($deliveryState === 'missing') {
            throw new WebhookVerificationException(WebhookVerificationException::DELIVERY_HEADER_MISSING, 'The X-Lettermint-Delivery header is missing.');
        }
        if ($deliveryState === 'ambiguous') {
            throw new WebhookVerificationException(WebhookVerificationException::DELIVERY_TIMESTAMP_MISMATCH, 'The request has more than one X-Lettermint-Delivery header.');
        }

        return $this->verifySignature($rawBody, (string) $signature, (string) $delivery);
    }

    /**
     * Verifies the raw body against an `X-Lettermint-Signature` value, for
     * setups where the headers are not at hand. When `$timestamp` (the
     * `X-Lettermint-Delivery` value) is given, it must equal the signed timestamp.
     *
     * @throws WebhookVerificationException When the delivery is not genuine.
     */
    public function verifySignature(string $rawBody, string $signatureHeader, string|int|null $timestamp = null): WebhookPayload
    {
        if (trim($signatureHeader) === '') {
            throw new WebhookVerificationException(WebhookVerificationException::SIGNATURE_HEADER_MISSING, 'The X-Lettermint-Signature header is missing.');
        }
        [$signedAt, $signatures] = self::parseSignatureHeader($signatureHeader);
        if ($timestamp !== null && trim((string) $timestamp) !== $signedAt) {
            throw new WebhookVerificationException(WebhookVerificationException::DELIVERY_TIMESTAMP_MISMATCH, 'The X-Lettermint-Delivery header does not match the signed timestamp.');
        }
        if ($rawBody === '') {
            throw new WebhookVerificationException(WebhookVerificationException::BODY_INVALID, 'The raw request body is empty.');
        }
        if (abs(($this->clock)() - (int) $signedAt) > $this->tolerance) {
            throw new WebhookVerificationException(WebhookVerificationException::TIMESTAMP_OUT_OF_TOLERANCE, 'The signed timestamp is outside the allowed tolerance.');
        }

        /** @var string $secret */
        $secret = $this->secret->getValue();
        $expected = hash_hmac('sha256', $signedAt.'.'.$rawBody, $secret);
        $matched = false;
        foreach ($signatures as $candidate) {
            if (hash_equals($expected, $candidate)) {
                $matched = true;
            }
        }
        if (! $matched) {
            throw new WebhookVerificationException(WebhookVerificationException::SIGNATURE_MISMATCH, 'The webhook signature does not match.');
        }

        try {
            $payload = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new WebhookVerificationException(WebhookVerificationException::PAYLOAD_INVALID, 'The webhook payload is not valid JSON.');
        }
        if (! is_array($payload) || ! str_starts_with(ltrim($rawBody), '{')) {
            throw new WebhookVerificationException(WebhookVerificationException::PAYLOAD_INVALID, 'The webhook payload is not a JSON object.');
        }
        if (! is_string($payload['event'] ?? null)) {
            throw new WebhookVerificationException(WebhookVerificationException::PAYLOAD_INVALID, 'The webhook payload has no event name.');
        }

        /** @var array<string, mixed> $payload */
        return new WebhookPayload($payload);
    }

    /**
     * Shows the tolerance; never the secret.
     *
     * @return array{tolerance: int}
     */
    public function __debugInfo(): array
    {
        return ['tolerance' => $this->tolerance];
    }

    /**
     * @return never
     */
    public function __serialize(): array
    {
        throw new LettermintConfigException('A Webhook cannot be serialized, because it holds the signing secret.');
    }

    /**
     * @return array{0: string, 1: list<string>} The signed timestamp and the v1 signatures (lowercase hex).
     */
    private static function parseSignatureHeader(string $header): array
    {
        $malformed = static fn (string $detail): WebhookVerificationException => new WebhookVerificationException(
            WebhookVerificationException::SIGNATURE_HEADER_MALFORMED,
            "The signature header is malformed: {$detail}.",
        );
        if (preg_match(self::PRINTABLE_ASCII, $header) !== 1) {
            throw $malformed('it contains non-ASCII or control characters');
        }
        $timestamp = null;
        $signatures = [];
        foreach (explode(',', $header) as $part) {
            $entry = trim($part);
            $separator = strpos($entry, '=');
            if ($separator === false) {
                continue;
            }
            $key = substr($entry, 0, $separator);
            $value = substr($entry, $separator + 1);
            if ($key === 't') {
                if ($timestamp !== null) {
                    throw $malformed('it has more than one timestamp');
                }
                if (preg_match(self::DIGITS, $value) !== 1 || strlen(ltrim($value, '0')) > 16 || (int) $value > self::MAX_TIMESTAMP) {
                    throw $malformed('the timestamp is not a number of seconds');
                }
                $timestamp = $value;
            } elseif ($key === 'v1' && preg_match(self::HEX_SHA256, $value) === 1) {
                $signatures[] = strtolower($value);
            }
        }
        if ($timestamp === null) {
            throw $malformed('the timestamp (t=) is missing');
        }
        if ($signatures === []) {
            throw $malformed('no v1 signature is present');
        }

        return [$timestamp, $signatures];
    }

    /**
     * Normalises the supported header containers to `[[name, value], ...]`.
     *
     * @param  array<array-key, mixed>|object  $headers
     * @return list<array{0: string, 1: mixed}>
     */
    private static function headerList(array|object $headers): array
    {
        if ($headers instanceof MessageInterface) {
            $headers = $headers->getHeaders();
        } elseif (is_object($headers) && ! $headers instanceof Traversable && method_exists($headers, 'all')) {
            $headers = $headers->all(); // Symfony and Laravel HeaderBag
        }
        if ($headers instanceof Traversable) {
            $headers = iterator_to_array($headers);
        }
        if (! is_array($headers)) {
            throw new LettermintConfigException('Pass the request headers as an array, a Symfony HeaderBag or a PSR-7 message.');
        }
        $list = [];
        foreach ($headers as $name => $value) {
            if (is_string($name)) {
                $list[] = [$name, $value];
            }
        }

        return $list;
    }

    /**
     * @param  list<array{0: string, 1: mixed}>  $headers
     * @return array{0: 'ambiguous'|'missing'|'value', 1: string|null}
     */
    private static function header(array $headers, string $name): array
    {
        $values = [];
        foreach ($headers as [$key, $value]) {
            if (strtolower($key) !== $name || $value === null) {
                continue;
            }
            foreach (is_array($value) ? $value : [$value] as $item) {
                if ($item !== null) {
                    $values[] = $item;
                }
            }
        }
        if ($values === []) {
            return ['missing', null];
        }
        if (count($values) > 1 || ! (is_string($values[0]) || is_int($values[0]))) {
            return ['ambiguous', null];
        }

        return ['value', (string) $values[0]];
    }
}
