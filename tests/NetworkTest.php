<?php

declare(strict_types=1);

use Lettermint\Exceptions\ConnectionException;
use Lettermint\Exceptions\RedirectException;
use Lettermint\Exceptions\TimeoutException;
use Lettermint\Lettermint;

/*
 * These tests send real HTTP requests with Guzzle's default handler to a local
 * `php -S` server, to check what a fake handler cannot: that redirects are not
 * followed and that the timeout covers the whole request.
 */

const MINIMAL = ['from' => 'a@example.test', 'to' => ['b@example.test'], 'subject' => 'x', 'text' => 'x'];

beforeAll(function () {
    $socket = stream_socket_server('tcp://127.0.0.1:0');
    $port = (int) substr((string) stream_socket_get_name($socket, false), strlen('127.0.0.1:'));
    fclose($socket);
    $capture = tempnam(sys_get_temp_dir(), 'lettermint-capture-');
    unlink($capture);
    $process = proc_open(
        [PHP_BINARY, '-S', "127.0.0.1:{$port}", __DIR__.'/Support/server.php'],
        [['pipe', 'r'], ['file', PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null', 'w'], ['file', PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null', 'w']],
        $pipes,
        null,
        ['PHP_CLI_SERVER_WORKERS' => '4', 'LETTERMINT_TEST_CAPTURE' => $capture] + getenv(),
    );
    for ($i = 0; $i < 100 && @fsockopen('127.0.0.1', $port) === false; $i++) {
        usleep(50_000);
    }
    $GLOBALS['lettermintTestServer'] = ['process' => $process, 'origin' => "http://127.0.0.1:{$port}", 'capture' => $capture];
});

afterAll(function () {
    $server = $GLOBALS['lettermintTestServer'];
    proc_terminate($server['process']);
    proc_close($server['process']);
    @unlink($server['capture']);
});

function server(string $mode, array $options = []): Lettermint
{
    $origin = $GLOBALS['lettermintTestServer']['origin'];

    return new Lettermint(...['sendingToken' => SENDING_TOKEN, 'teamToken' => TEAM_TOKEN, 'baseUrl' => "{$origin}/{$mode}/v1", ...$options]);
}

it('sends to the base URL path with the real handler', function () {
    $result = server('ok')->emails->send(MINIMAL);
    expect($result->message_id)->toBe('msg_ok')
        ->and($result['path'])->toBe('/ok/v1/send');
});

it('never follows a redirect, so the token stays at the API origin', function () {
    $error = thrown(fn () => server('redirect')->emails->send(MINIMAL));
    expect($error)->toBeInstanceOf(RedirectException::class)
        ->and($error->status)->toBe(307)
        ->and(file_exists($GLOBALS['lettermintTestServer']['capture']))->toBeFalse();
    expect(fn () => server('redirect')->ping())->toThrow(RedirectException::class)
        ->and(file_exists($GLOBALS['lettermintTestServer']['capture']))->toBeFalse();
});

it('times out while waiting for the response', function () {
    $started = microtime(true);
    $error = thrown(fn () => server('slow', ['timeout' => 0.5])->emails->send(MINIMAL));
    expect($error)->toBeInstanceOf(TimeoutException::class)
        ->and($error->timeout)->toBe(0.5)
        ->and(microtime(true) - $started)->toBeLessThan(2.5);
});

it('times out while reading the body', function () {
    $started = microtime(true);
    $error = thrown(fn () => server('slowbody', ['timeout' => 0.5])->emails->send(MINIMAL));
    expect($error)->toBeInstanceOf(TimeoutException::class)
        ->and(microtime(true) - $started)->toBeLessThan(2.5);
});

it('wraps a refused connection in ConnectionException', function () {
    $socket = stream_socket_server('tcp://127.0.0.1:0');
    $port = (int) substr((string) stream_socket_get_name($socket, false), strlen('127.0.0.1:'));
    fclose($socket);
    $lettermint = new Lettermint(sendingToken: SENDING_TOKEN, baseUrl: "http://127.0.0.1:{$port}/v1", timeout: 2);
    $error = thrown(fn () => $lettermint->emails->send(MINIMAL));
    expect($error)->toBeInstanceOf(ConnectionException::class)
        ->and($error->getMessage())->toStartWith('Could not reach the Lettermint API: ');
    expectNoSecrets($error);
});
