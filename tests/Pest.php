<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Lettermint\Lettermint;
use Lettermint\Tests\Support\FakeApi;
use Lettermint\Tests\Support\RecordedRequest;
use PHPUnit\Framework\AssertionFailedError;

const SENDING_TOKEN = 'lm_sendingFixture0000000000000000';
const TEAM_TOKEN = 'lm_team_teamFixture000000000000000000000000000';
const BASE_URL = 'https://api.lettermint.co/v1';

/**
 * A client with both tokens (unless overridden) whose Guzzle handler is a FakeApi.
 *
 * @param  (callable(RecordedRequest, int): mixed)|null  $handler
 * @param  array<string, mixed>  $options  Lettermint constructor arguments.
 * @return array{0: Lettermint, 1: FakeApi}
 */
function client(?callable $handler = null, array $options = []): array
{
    $api = new FakeApi($handler);
    $lettermint = new Lettermint(...[
        'sendingToken' => SENDING_TOKEN,
        'teamToken' => TEAM_TOKEN,
        ...$options,
        'httpClient' => $api->client(),
    ]);

    return [$lettermint, $api];
}

/**
 * @param  array<array-key, mixed>|null  $body
 * @param  array<string, string>  $headers
 */
function json(int $status, mixed $body, array $headers = []): Response
{
    return new Response($status, ['Content-Type' => 'application/json', ...$headers], json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
}

/**
 * @param  array<string, string>  $headers
 */
function text(int $status, string $body, array $headers = [], ?string $reason = null): Response
{
    return new Response($status, $headers, $body, '1.1', $reason);
}

/**
 * Returns the exception thrown by `$fn`, or fails the test when it does not throw.
 */
function thrown(callable $fn): Throwable
{
    try {
        $fn();
    } catch (Throwable $exception) {
        return $exception;
    }

    throw new AssertionFailedError('Expected the callable to throw.');
}

/**
 * Every debug and export representation of a value that PHP offers.
 *
 * For exceptions, the stack frames of the test harness are left out: they hold
 * the test's own arguments (such as a token from a dataset). The frames of the
 * SDK, with their arguments, are kept.
 *
 * @return array<string, string>
 */
function renderings(mixed $value): array
{
    if ($value instanceof Throwable) {
        return exceptionRenderings($value);
    }
    $outputs = [];
    ob_start();
    var_dump($value);
    $outputs['var_dump'] = (string) ob_get_clean();
    $outputs['print_r'] = print_r($value, true);
    try {
        $outputs['var_export'] = var_export($value, true);
    } catch (Throwable $exception) {
        $outputs['var_export'] = 'threw '.$exception::class;
    }
    $outputs['json_encode'] = (string) json_encode($value);
    try {
        $outputs['serialize'] = serialize($value);
    } catch (Throwable $exception) {
        $outputs['serialize'] = 'threw '.$exception::class.': '.$exception->getMessage();
    }
    if (is_object($value)) {
        ob_start();
        var_dump((array) $value);
        $outputs['(array) cast'] = (string) ob_get_clean();
    }

    return $outputs;
}

/**
 * @return array<string, string>
 */
function exceptionRenderings(Throwable $exception): array
{
    $outputs = [];
    for ($current = $exception, $depth = 0; $current !== null; $current = $current->getPrevious(), $depth++) {
        $frames = array_values(array_filter(
            $current->getTrace(),
            static fn (array $frame): bool => str_starts_with($frame['class'] ?? '', 'Lettermint\\') && ! str_starts_with($frame['class'] ?? '', 'Lettermint\\Tests\\'),
        ));
        ob_start();
        var_dump($frames);
        $outputs["#{$depth} SDK frames var_dump"] = (string) ob_get_clean();
        $outputs["#{$depth} SDK frames var_export"] = var_export($frames, true);
        $outputs["#{$depth} message"] = $current->getMessage();
        ob_start();
        var_dump(get_object_vars($current));
        $outputs["#{$depth} properties"] = (string) ob_get_clean();
        $outputs["#{$depth} json_encode"] = (string) json_encode($current);
    }

    return $outputs;
}

function expectNoSecrets(mixed $value, string ...$secrets): void
{
    $secrets = $secrets === [] ? [SENDING_TOKEN, TEAM_TOKEN] : $secrets;
    foreach (renderings($value) as $how => $output) {
        foreach ($secrets as $secret) {
            expect(str_contains($output, $secret))->toBeFalse("{$how} shows a secret");
        }
    }
}
