<?php

namespace Lettermint;

use Lettermint\Exceptions\InvalidSignatureException;
use Lettermint\Exceptions\JsonDecodeException;
use Lettermint\Exceptions\TimestampToleranceException;
use Lettermint\Exceptions\WebhookVerificationException;

final class Webhook
{
    private const SIGNATURE_HEADER = 'X-Lettermint-Signature';

    private const DELIVERY_HEADER = 'X-Lettermint-Delivery';

    private const DEFAULT_TOLERANCE = 300;

    private string $secret;

    private int $tolerance;

    /**
     * Create a new webhook verifier instance.
     *
     * @param  string  $secret  The webhook signing secret
     * @param  int  $tolerance  Maximum allowed time difference in seconds (default: 300)
     *
     * @throws \InvalidArgumentException If secret is empty
     */
    public function __construct(string $secret, int $tolerance = self::DEFAULT_TOLERANCE)
    {
        if ($secret === '') {
            throw new \InvalidArgumentException('Webhook secret cannot be empty');
        }

        $this->secret = $secret;
        $this->tolerance = $tolerance;
    }

    /**
     * Verify a webhook signature and return the decoded payload.
     *
     * @param  string  $payload  The raw request body
     * @param  string  $signature  The signature header value (format: t={timestamp},v1={hash})
     * @param  int|null  $timestamp  Optional timestamp from delivery header for cross-validation
     * @return array<string, mixed> The decoded webhook payload
     *
     * @throws WebhookVerificationException If signature format is invalid or timestamps mismatch
     * @throws InvalidSignatureException If signature doesn't match
     * @throws TimestampToleranceException If timestamp is outside tolerance window
     * @throws JsonDecodeException If payload is not valid JSON
     */
    public function verify(string $payload, string $signature, ?int $timestamp = null): array
    {
        $parsedSignature = $this->parseSignature($signature);

        $signatureTimestamp = $parsedSignature['timestamp'];
        $expectedSignature = $parsedSignature['signature'];

        if ($timestamp !== null && $timestamp !== $signatureTimestamp) {
            throw new WebhookVerificationException('Timestamp mismatch between signature and delivery headers');
        }

        $this->validateTimestamp($signatureTimestamp);

        $signedContent = $signatureTimestamp.'.'.$payload;
        $computedSignature = hash_hmac('sha256', $signedContent, $this->secret);

        if (! hash_equals($computedSignature, $expectedSignature)) {
            throw new InvalidSignatureException('Signature verification failed');
        }

        $data = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonDecodeException('Failed to decode webhook payload: '.json_last_error_msg());
        }

        return $data;
    }

    /**
     * Verify a webhook using HTTP headers and return the decoded payload.
     *
     * @param  array<string, string>  $headers  HTTP headers from the request
     * @param  string  $payload  The raw request body
     * @return array<string, mixed> The decoded webhook payload
     *
     * @throws WebhookVerificationException If required headers are missing or verification fails
     * @throws InvalidSignatureException If signature doesn't match
     * @throws TimestampToleranceException If timestamp is outside tolerance window
     * @throws JsonDecodeException If payload is not valid JSON
     */
    public function verifyHeaders(array $headers, string $payload): array
    {
        $headers = $this->normalizeHeaders($headers);

        $signature = $headers[strtolower(self::SIGNATURE_HEADER)] ?? null;
        $timestamp = $headers[strtolower(self::DELIVERY_HEADER)] ?? null;

        if ($signature === null) {
            throw new WebhookVerificationException('Missing signature header: '.self::SIGNATURE_HEADER);
        }

        if ($timestamp === null) {
            throw new WebhookVerificationException('Missing delivery header: '.self::DELIVERY_HEADER);
        }

        return $this->verify($payload, $signature, (int) $timestamp);
    }

    /**
     * Static convenience method to verify a webhook signature.
     *
     * @param  string  $payload  The raw request body
     * @param  string  $signature  The signature header value (format: t={timestamp},v1={hash})
     * @param  string  $secret  The webhook signing secret
     * @param  int|null  $timestamp  Optional timestamp from delivery header for cross-validation
     * @param  int  $tolerance  Maximum allowed time difference in seconds (default: 300)
     * @return array<string, mixed> The decoded webhook payload
     *
     * @throws \InvalidArgumentException If secret is empty
     * @throws WebhookVerificationException If signature format is invalid or timestamps mismatch
     * @throws InvalidSignatureException If signature doesn't match
     * @throws TimestampToleranceException If timestamp is outside tolerance window
     * @throws JsonDecodeException If payload is not valid JSON
     */
    public static function verifySignature(
        string $payload,
        string $signature,
        string $secret,
        ?int $timestamp = null,
        int $tolerance = self::DEFAULT_TOLERANCE
    ): array {
        $webhook = new self($secret, $tolerance);

        return $webhook->verify($payload, $signature, $timestamp);
    }

    private function parseSignature(string $signature): array
    {
        $parts = explode(',', $signature);

        $timestamp = null;
        $signatureHash = null;

        foreach ($parts as $part) {
            $keyValue = explode('=', $part, 2);
            if (count($keyValue) !== 2) {
                continue;
            }

            [$key, $value] = $keyValue;

            if ($key === 't') {
                $timestamp = (int) $value;
            } elseif ($key === 'v1') {
                $signatureHash = $value;
            }
        }

        if ($timestamp === null || $signatureHash === null) {
            throw new WebhookVerificationException('Invalid signature format. Expected format: t={timestamp},v1={signature}');
        }

        return [
            'timestamp' => $timestamp,
            'signature' => $signatureHash,
        ];
    }

    private function validateTimestamp(int $timestamp): void
    {
        $currentTime = time();
        $difference = abs($currentTime - $timestamp);

        if ($difference > $this->tolerance) {
            throw new TimestampToleranceException(
                sprintf(
                    'Timestamp outside tolerance window. Difference: %d seconds, Tolerance: %d seconds',
                    $difference,
                    $this->tolerance
                )
            );
        }
    }

    private function normalizeHeaders(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $key => $value) {
            $normalized[strtolower($key)] = $value;
        }

        return $normalized;
    }
}
