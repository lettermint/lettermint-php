<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Lettermint\Attachment;
use Lettermint\EmailBuilder;
use Lettermint\Exceptions\LettermintValidationException;
use Lettermint\Exceptions\ServerException;
use Lettermint\Exceptions\UnexpectedResponseException;
use Lettermint\Lettermint;
use Lettermint\Tests\Support\FakeApi;
use Lettermint\Tests\Support\RecordedRequest;
use Lettermint\Types\SendMailResponse;

function accepted(string $id = 'message_1'): Response
{
    return json(202, ['message_id' => $id, 'status' => 'pending']);
}

/**
 * @return array{0: Lettermint, 1: FakeApi}
 */
function sendingClient(): array
{
    return client(fn (RecordedRequest $request) => accepted('msg_'.$request->json()['subject']));
}

describe('emails->send', function () {
    it('posts exactly the message with the sending token', function () {
        [$lettermint, $api] = sendingClient();
        $message = ['from' => 'Acme <hello@acme.test>', 'to' => ['jane@example.test'], 'subject' => 'Welcome', 'html' => '<p>Hi</p>'];
        $result = $lettermint->emails->send($message);
        expect($result)->toBeInstanceOf(SendMailResponse::class)
            ->and($result->message_id)->toBe('msg_Welcome')
            ->and($result->status)->toBe('pending');
        $request = $api->last();
        expect($request->url)->toBe('https://api.lettermint.co/v1/send')
            ->and($request->method)->toBe('POST')
            ->and($request->json())->toBe($message)
            ->and($request->headers['x-lettermint-token'])->toBe(SENDING_TOKEN)
            ->and($request->headers['content-type'])->toBe('application/json')
            ->and($request->headers)->not->toHaveKey('authorization')
            ->and($request->headers)->not->toHaveKey('idempotency-key');
    });

    it('sends the idempotency key only with the call that sets it', function () {
        [$lettermint, $api] = sendingClient();
        $message = ['from' => 'a@example.test', 'to' => ['b@example.test'], 'subject' => 'One'];
        $lettermint->emails->send($message, idempotencyKey: 'order-123');
        $lettermint->emails->send([...$message, 'subject' => 'Two']);
        expect($api->requests[0]->headers['idempotency-key'])->toBe('order-123')
            ->and($api->requests[1]->headers)->not->toHaveKey('idempotency-key');
    });

    it('rejects an invalid idempotency key before sending', function (string $key) {
        [$lettermint, $api] = sendingClient();
        $error = thrown(fn () => $lettermint->emails->send(['from' => 'a@example.test', 'to' => ['b@example.test'], 'subject' => 'x'], $key));
        expect($error)->toBeInstanceOf(LettermintValidationException::class)
            ->and($error->field)->toBe('idempotencyKey')
            ->and($api->requests)->toBeEmpty();
    })->with(['', "line\nbreak", "nul\0"]);

    it('validates tags before any request', function () {
        [$lettermint, $api] = sendingClient();
        $error = thrown(fn () => $lettermint->emails->send([
            'from' => 'a@example.test', 'to' => ['b@example.test'], 'subject' => 'x',
            'tags' => [['name' => 'not valid!', 'value' => 'x']],
        ]));
        expect($error)->toBeInstanceOf(LettermintValidationException::class)
            ->and($error->field)->toBe('tags')
            ->and($api->requests)->toBeEmpty();
    });

    it('requires base64 attachment arrays and points to Attachment for raw bytes', function () {
        [$lettermint, $api] = sendingClient();
        $error = thrown(fn () => $lettermint->emails->send([
            'from' => 'a@example.test', 'to' => ['b@example.test'], 'subject' => 'x',
            'attachments' => [Attachment::fromBytes('a.txt', 'Hi')],
        ]));
        expect($error)->toBeInstanceOf(LettermintValidationException::class)
            ->and($error->field)->toBe('attachments[0]')
            ->and($error->getMessage())->toContain('Attachment::fromBytes(...)->toArray()');
        $lettermint->emails->send([
            'from' => 'a@example.test', 'to' => ['b@example.test'], 'subject' => 'x',
            'attachments' => [Attachment::fromBytes('a.txt', 'Hello World')->toArray()],
        ]);
        expect($api->last()->json()['attachments'])->toBe([['filename' => 'a.txt', 'content' => 'SGVsbG8gV29ybGQ=']]);
    });

    it('rejects a message that is a list', function () {
        [$lettermint] = sendingClient();
        expect(fn () => $lettermint->emails->send(['a@example.test']))->toThrow(LettermintValidationException::class);
    });
});

describe('emails->sendBatch', function () {
    it('posts the messages and builders as one batch with its own key', function () {
        [$lettermint, $api] = client(fn () => json(202, [
            ['message_id' => 'm1', 'status' => 'pending'],
            ['message_id' => 'm2', 'status' => 'scheduled', 'scheduled_at' => '2026-10-05T09:00:00Z'],
        ]));
        $first = ['from' => 'a@example.test', 'to' => ['b@example.test'], 'subject' => 'One'];
        $second = $lettermint->emails->compose()
            ->from('a@example.test')
            ->to('c@example.test')
            ->subject('Two')
            ->scheduledAt(new DateTimeImmutable('2026-10-05T11:00:00+02:00'));
        $results = $lettermint->emails->sendBatch([$first, $second], idempotencyKey: 'batch-1');
        expect($results)->toHaveCount(2)
            ->and($results[0])->toBeInstanceOf(SendMailResponse::class)
            ->and($results[1]->status)->toBe('scheduled')
            ->and($api->last()->url)->toBe('https://api.lettermint.co/v1/send/batch')
            ->and($api->last()->json())->toBe([
                $first,
                ['from' => 'a@example.test', 'to' => ['c@example.test'], 'subject' => 'Two', 'scheduled_at' => '2026-10-05T09:00:00.000Z'],
            ])
            ->and($api->last()->headers['idempotency-key'])->toBe('batch-1');
        $lettermint->emails->sendBatch([$first]);
        expect($api->last()->headers)->not->toHaveKey('idempotency-key');
    });

    it('names the invalid message', function () {
        [$lettermint, $api] = sendingClient();
        $error = thrown(fn () => $lettermint->emails->sendBatch([
            ['from' => 'a@example.test', 'to' => ['b@example.test'], 'subject' => 'ok'],
            ['from' => 'a@example.test', 'to' => ['b@example.test'], 'subject' => 'bad', 'tags' => 'x'],
        ]));
        expect($error)->toBeInstanceOf(LettermintValidationException::class)
            ->and($error->field)->toBe('messages[1].tags')
            ->and($api->requests)->toBeEmpty();
    });

    it('requires a JSON list in the response', function () {
        [$lettermint] = client(fn () => json(202, ['message_id' => 'm', 'status' => 'pending']));
        expect(fn () => $lettermint->emails->sendBatch([['from' => 'a@example.test', 'to' => ['b@example.test'], 'subject' => 'x']]))
            ->toThrow(UnexpectedResponseException::class, 'not a JSON list');
    });
});

describe('emails->compose', function () {
    it('sets every field', function () {
        [$lettermint, $api] = sendingClient();
        $lettermint->emails->compose()
            ->from('John Doe <john@example.test>')
            ->to('to1@example.test', 'to2@example.test')
            ->cc('cc@example.test')
            ->bcc('bcc@example.test')
            ->replyTo('reply@example.test')
            ->subject('Everything')
            ->html('<h1>Hello</h1>')
            ->text('Hello')
            ->headers(['X-Custom' => 'Value'])
            ->metadata(['foo' => 'bar'])
            ->tag('campaign-123')
            ->tags([['name' => 'campaign', 'value' => 'welcome']])
            ->route('transactional')
            ->scheduledAt('tomorrow 9am')
            ->settings(['track_opens' => false, 'track_clicks' => true, 'tls' => 'enforced'])
            ->sandboxResult('clicked')
            ->attach(Attachment::fromBase64('a.txt', 'SGk='))
            ->attach(new Attachment('logo.png', 'PNG', 'image/png', 'logo'))
            ->attach(['filename' => 'c.txt', 'content' => 'Qw==', 'content_type' => 'text/plain'])
            ->send(idempotencyKey: 'unique-id-123');
        expect($api->last()->json())->toBe([
            'from' => 'John Doe <john@example.test>',
            'to' => ['to1@example.test', 'to2@example.test'],
            'subject' => 'Everything',
            'cc' => ['cc@example.test'],
            'bcc' => ['bcc@example.test'],
            'reply_to' => ['reply@example.test'],
            'html' => '<h1>Hello</h1>',
            'text' => 'Hello',
            'headers' => ['X-Custom' => 'Value'],
            'metadata' => ['foo' => 'bar'],
            'tag' => 'campaign-123',
            'tags' => [['name' => 'campaign', 'value' => 'welcome']],
            'route' => 'transactional',
            'scheduled_at' => 'tomorrow 9am',
            'settings' => ['track_opens' => false, 'track_clicks' => true, 'tls' => 'enforced'],
            'sandbox_result' => 'clicked',
            'attachments' => [
                ['filename' => 'a.txt', 'content' => 'SGk='],
                ['filename' => 'logo.png', 'content' => 'UE5H', 'content_type' => 'image/png', 'content_id' => 'logo'],
                ['filename' => 'c.txt', 'content' => 'Qw==', 'content_type' => 'text/plain'],
            ],
        ])->and($api->last()->headers['idempotency-key'])->toBe('unique-id-123');
    });

    it('returns a new builder from every setter', function () {
        [$lettermint] = sendingClient();
        $base = $lettermint->emails->compose();
        $next = $base->from('a@example.test');
        expect($next)->not->toBe($base)
            ->and($base->build()['from'])->toBe('')
            ->and($next->build()['from'])->toBe('a@example.test');
    });

    it('reuses a base builder for two recipients without mixing them', function () {
        [$lettermint, $api] = sendingClient();
        $base = $lettermint->emails->compose()->from('Acme <hi@acme.test>')->subject('Welcome');
        $base->to('jane@example.test')->html('<p>Jane</p>')->send();
        $base->to('john@example.test')->html('<p>John</p>')->send('welcome-john');
        expect($api->requests[0]->json())->toBe(['from' => 'Acme <hi@acme.test>', 'to' => ['jane@example.test'], 'subject' => 'Welcome', 'html' => '<p>Jane</p>'])
            ->and($api->requests[0]->headers)->not->toHaveKey('idempotency-key')
            ->and($api->requests[1]->json())->toBe(['from' => 'Acme <hi@acme.test>', 'to' => ['john@example.test'], 'subject' => 'Welcome', 'html' => '<p>John</p>'])
            ->and($api->requests[1]->headers['idempotency-key'])->toBe('welcome-john')
            ->and($base->build())->toBe(['from' => 'Acme <hi@acme.test>', 'to' => [], 'subject' => 'Welcome']);
    });

    it('does not accumulate attachments across derived builders', function () {
        [$lettermint] = sendingClient();
        $base = $lettermint->emails->compose()->attach(Attachment::fromBase64('a.txt', 'QQ=='));
        $one = $base->attach(Attachment::fromBase64('b.txt', 'Qg=='));
        $two = $base->attach(Attachment::fromBase64('c.txt', 'Qw=='));
        $names = fn (EmailBuilder $builder): array => array_column($builder->build()['attachments'] ?? [], 'filename');
        expect($names($base))->toBe(['a.txt'])
            ->and($names($one))->toBe(['a.txt', 'b.txt'])
            ->and($names($two))->toBe(['a.txt', 'c.txt']);
    });

    it('keeps the base builder unchanged when a setter throws', function () {
        [$lettermint, $api] = sendingClient();
        $base = $lettermint->emails->compose()->from('a@example.test')->to('b@example.test')->subject('Base')->tags([['name' => 'ok', 'value' => 'yes']]);
        $before = $base->build();
        $error = thrown(fn () => $base->tags([['name' => 'not a valid tag name!', 'value' => 'x']]));
        expect($error)->toBeInstanceOf(LettermintValidationException::class)
            ->and($base->build())->toBe($before);
        $base->send();
        expect($api->last()->json())->toBe($before);
    });

    it('removes html, text, tag and the schedule with null', function () {
        [$lettermint] = sendingClient();
        $builder = $lettermint->emails->compose()
            ->html('<p>x</p>')->text('x')->tag('t')->scheduledAt('tomorrow')
            ->html(null)->text(null)->tag(null)->scheduledAt(null);
        expect($builder->build())->toBe(['from' => '', 'to' => [], 'subject' => '']);
    });

    it('starts from a message', function () {
        [$lettermint] = sendingClient();
        $message = ['from' => 'a@example.test', 'to' => ['b@example.test'], 'subject' => 'Hi'];
        $builder = $lettermint->emails->compose($message);
        expect($builder->to('d@example.test')->build())->toBe(['from' => 'a@example.test', 'to' => ['d@example.test'], 'subject' => 'Hi'])
            ->and($builder->build()['to'])->toBe(['b@example.test']);
    });

    it('reads attachments from bytes and files', function () {
        [$lettermint] = sendingClient();
        $file = tempnam(sys_get_temp_dir(), 'lettermint-');
        file_put_contents($file, "\x00\xff\x80");
        $built = $lettermint->emails->compose()
            ->attach(Attachment::fromBytes('a.txt', 'Hello World'))
            ->attach(Attachment::fromPath($file, 'b.bin', 'application/octet-stream'))
            ->build();
        unlink($file);
        expect($built['attachments'] ?? null)->toBe([
            ['filename' => 'a.txt', 'content' => 'SGVsbG8gV29ybGQ='],
            ['filename' => 'b.bin', 'content' => 'AP+A', 'content_type' => 'application/octet-stream'],
        ]);
        expect(fn () => Attachment::fromPath('/does/not/exist.pdf'))->toThrow(LettermintValidationException::class, 'cannot be read');
        expect(fn () => new Attachment('', 'x'))->toThrow(LettermintValidationException::class, 'needs a filename');
    });

    it('serializes and dumps the message without credentials', function () {
        [$lettermint] = sendingClient();
        $builder = $lettermint->emails->compose()->from('a@example.test')->subject('Hi');
        expect(json_decode((string) json_encode($builder), true))->toBe(['from' => 'a@example.test', 'to' => [], 'subject' => 'Hi']);
        ob_start();
        var_dump($builder);
        $dump = (string) ob_get_clean();
        expect($dump)->toContain('a@example.test');
        expectNoSecrets($builder);
    });
});

describe('message tags', function () {
    $maxTags = array_map(fn (int $i): array => ['name' => "tag{$i}", 'value' => 'v'], range(0, 19));

    it('rejects invalid tags', function (array $tags, string $message) {
        [$lettermint] = sendingClient();
        $error = thrown(fn () => $lettermint->emails->compose()->tags($tags));
        expect($error)->toBeInstanceOf(LettermintValidationException::class)
            ->and($error->getMessage())->toContain($message);
    })->with([
        'duplicate' => [[['name' => 'duplicate', 'value' => 'one'], ['name' => 'duplicate', 'value' => 'two']], 'unique'],
        'reserved' => [[['name' => '__LETTERMint_internal', 'value' => 'one']], '__lettermint'],
        'space in name' => [[['name' => 'invalid name', 'value' => 'one']], 'names must match'],
        'long name' => [[['name' => str_repeat('x', 33), 'value' => 'one']], 'names must match'],
        'space in value' => [[['name' => 'valid', 'value' => 'invalid value']], 'values must match'],
        'long value' => [[['name' => 'valid', 'value' => str_repeat('x', 65)]], 'values must match'],
        'no value' => [[['name' => 'valid']], 'arrays with string values'],
        'too many' => [[...array_map(fn (int $i): array => ['name' => "tag{$i}", 'value' => 'v'], range(0, 19)), ['name' => 'one-more', 'value' => 'v']], 'No more than 20'],
    ]);

    it('allows 20 tags, or 19 with a legacy tag', function () use ($maxTags) {
        [$lettermint] = sendingClient();
        $builder = $lettermint->emails->compose()->tags($maxTags);
        expect(fn () => $builder->tag('legacy'))->toThrow(LettermintValidationException::class, 'A legacy tag and no more than 19')
            ->and(fn () => $lettermint->emails->compose()->tag('legacy')->tags($maxTags))->toThrow(LettermintValidationException::class, 'A legacy tag and no more than 19')
            ->and($lettermint->emails->compose()->tag('legacy')->tags(array_slice($maxTags, 1))->build()['tags'] ?? [])->toHaveCount(19);
    });

    it('accepts case-sensitive duplicates', function () {
        [$lettermint] = sendingClient();
        $tags = [['name' => 'Campaign', 'value' => 'a'], ['name' => 'campaign', 'value' => 'b']];
        expect($lettermint->emails->compose()->tags($tags)->build()['tags'] ?? null)->toBe($tags);
    });
});

describe('concurrency', function () {
    it('keeps two emails composed in interleaved fibers on one client separate', function () {
        [$lettermint, $api] = client(fn (RecordedRequest $request) => accepted('msg_'.$request->json()['subject']));
        $compose = function (string $label, array $recipients) use ($lettermint): SendMailResponse {
            $builder = $lettermint->emails->compose();
            $builder = $builder->from("{$label} <".strtolower($label).'@example.test>');
            Fiber::suspend();
            $builder = $builder->to(...$recipients);
            Fiber::suspend();
            $builder = $builder->subject($label)->html("<p>{$label}</p>");
            Fiber::suspend();
            $builder = $builder->attach(Attachment::fromBase64("{$label}.txt", 'SGk='));
            Fiber::suspend();

            return $builder->send("key-{$label}");
        };
        $fibers = ['A' => new Fiber(fn () => $compose('A', ['a1@example.test', 'a2@example.test'])), 'B' => new Fiber(fn () => $compose('B', ['b1@example.test']))];
        foreach ($fibers as $fiber) {
            $fiber->start();
        }
        while (array_filter($fibers, fn (Fiber $fiber): bool => ! $fiber->isTerminated()) !== []) {
            foreach ($fibers as $fiber) {
                if (! $fiber->isTerminated()) {
                    $fiber->resume();
                }
            }
        }
        expect($fibers['A']->getReturn()->message_id)->toBe('msg_A')
            ->and($fibers['B']->getReturn()->message_id)->toBe('msg_B');
        $bySubject = [];
        foreach ($api->requests as $request) {
            $bySubject[$request->json()['subject']] = $request;
        }
        expect($bySubject['A']->json())->toBe([
            'from' => 'A <a@example.test>', 'to' => ['a1@example.test', 'a2@example.test'], 'subject' => 'A', 'html' => '<p>A</p>',
            'attachments' => [['filename' => 'A.txt', 'content' => 'SGk=']],
        ])->and($bySubject['B']->json())->toBe([
            'from' => 'B <b@example.test>', 'to' => ['b1@example.test'], 'subject' => 'B', 'html' => '<p>B</p>',
            'attachments' => [['filename' => 'B.txt', 'content' => 'SGk=']],
        ])->and($bySubject['A']->headers['idempotency-key'])->toBe('key-A')
            ->and($bySubject['B']->headers['idempotency-key'])->toBe('key-B');
    });

    it('leaves nothing behind after a failed request', function () {
        [$lettermint, $api] = client(fn (RecordedRequest $request, int $index) => $index === 0 ? json(500, ['message' => 'Server Error']) : accepted());
        $error = thrown(fn () => $lettermint->emails->compose()->from('a@example.test')->to('b@example.test')->cc('cc@example.test')->subject('A')->send('key-A'));
        expect($error)->toBeInstanceOf(ServerException::class);
        $lettermint->emails->compose()->from('c@example.test')->to('d@example.test')->subject('C')->send();
        expect($api->requests[1]->json())->toBe(['from' => 'c@example.test', 'to' => ['d@example.test'], 'subject' => 'C'])
            ->and($api->requests[1]->headers)->not->toHaveKey('idempotency-key');
    });
});
