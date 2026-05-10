<?php

use Lettermint\Client\HttpClient;
use Lettermint\Endpoints\EmailEndpoint;
use Lettermint\Responses\SendBatchMailResponse;
use Lettermint\Responses\SendMailResponse;

beforeEach(function () {
    $this->httpClient = Mockery::mock(HttpClient::class);
    $this->endpoint = new EmailEndpoint($this->httpClient);
});

afterEach(function () {
    Mockery::close();
});

test('it exposes typed send response classes', function () {
    $classReflection = new ReflectionClass(EmailEndpoint::class);
    $classDocComment = $classReflection->getDocComment();
    $methodReflection = new ReflectionMethod(EmailEndpoint::class, 'send');
    $batchMethodReflection = new ReflectionMethod(EmailEndpoint::class, 'sendBatch');

    expect($classDocComment)
        ->toBeString()
        ->toContain('@phpstan-import-type SendMailRequest')
        ->toContain('@phpstan-import-type SendBatchMailRequest')
        ->not->toContain('@phpstan-type SendResponse');

    expect($methodReflection->getReturnType()?->getName())->toBe(SendMailResponse::class);
    expect($batchMethodReflection->getReturnType()?->getName())->toBe(SendBatchMailResponse::class);
});

test('it builds email with basic required fields', function () {
    $this->httpClient
        ->shouldReceive('post')
        ->once()
        ->with('/v1/send', [
            'from' => 'sender@example.com',
            'to' => ['recipient@example.com'],
            'subject' => 'Test Subject',
        ], [])
        ->andReturn(['message_id' => '123', 'status' => 'pending']);

    $response = $this->endpoint
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('Test Subject')
        ->send();

    expect($response->toArray())->toBe(['message_id' => '123', 'status' => 'pending']);
});

test('it supports multiple recipients', function () {
    $this->httpClient
        ->shouldReceive('post')
        ->once()
        ->with('/v1/send', [
            'from' => 'sender@example.com',
            'to' => ['recipient1@example.com', 'recipient2@example.com'],
            'subject' => 'Test Subject',
        ], [])
        ->andReturn(['message_id' => '123', 'status' => 'pending']);

    $this->endpoint
        ->from('sender@example.com')
        ->to('recipient1@example.com', 'recipient2@example.com')
        ->subject('Test Subject')
        ->send();
});

test('it handles HTML and text content', function () {
    $this->httpClient
        ->shouldReceive('post')
        ->once()
        ->with('/v1/send', [
            'from' => 'sender@example.com',
            'to' => ['recipient@example.com'],
            'subject' => 'Test Subject',
            'html' => '<p>HTML content</p>',
            'text' => 'Plain text content',
        ], [])
        ->andReturn(['message_id' => '123', 'status' => 'pending']);

    $this->endpoint
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('Test Subject')
        ->html('<p>HTML content</p>')
        ->text('Plain text content')
        ->send();
});

test('it handles CC recipients', function () {
    $this->httpClient
        ->shouldReceive('post')
        ->once()
        ->with('/v1/send', [
            'from' => 'sender@example.com',
            'to' => ['recipient@example.com'],
            'subject' => 'Test Subject',
            'cc' => ['cc1@example.com', 'cc2@example.com'],
        ], [])
        ->andReturn(['message_id' => '123', 'status' => 'pending']);

    $this->endpoint
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('Test Subject')
        ->cc('cc1@example.com', 'cc2@example.com')
        ->send();
});

test('it handles BCC recipients', function () {
    $this->httpClient
        ->shouldReceive('post')
        ->once()
        ->with('/v1/send', [
            'from' => 'sender@example.com',
            'to' => ['recipient@example.com'],
            'subject' => 'Test Subject',
            'bcc' => ['bcc1@example.com', 'bcc2@example.com'],
        ], [])
        ->andReturn(['message_id' => '123', 'status' => 'pending']);

    $this->endpoint
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('Test Subject')
        ->bcc('bcc1@example.com', 'bcc2@example.com')
        ->send();
});

test('it handles reply-to addresses', function () {
    $this->httpClient
        ->shouldReceive('post')
        ->once()
        ->with('/v1/send', [
            'from' => 'sender@example.com',
            'to' => ['recipient@example.com'],
            'subject' => 'Test Subject',
            'reply_to' => ['reply@example.com'],
        ], [])
        ->andReturn(['message_id' => '123', 'status' => 'pending']);

    $this->endpoint
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('Test Subject')
        ->replyTo('reply@example.com')
        ->send();
});

test('it handles attachments', function () {
    $attachment = [
        'filename' => 'test.pdf',
        'content' => 'base64encodedcontent',
    ];

    $this->httpClient
        ->shouldReceive('post')
        ->once()
        ->with('/v1/send', [
            'from' => 'sender@example.com',
            'to' => ['recipient@example.com'],
            'subject' => 'Test Subject',
            'attachments' => [$attachment],
        ], [])
        ->andReturn(['message_id' => '123', 'status' => 'pending']);

    $this->endpoint
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('Test Subject')
        ->attach('test.pdf', 'base64encodedcontent')
        ->send();
});

test('it handles attachments with content_id', function () {
    $attachment = [
        'filename' => 'logo.png',
        'content' => 'base64encodedimage',
        'content_id' => 'logo@example.com',
    ];

    $this->httpClient
        ->shouldReceive('post')
        ->once()
        ->with('/v1/send', [
            'from' => 'sender@example.com',
            'to' => ['recipient@example.com'],
            'subject' => 'Test Subject',
            'html' => '<img src="cid:logo@example.com">',
            'attachments' => [$attachment],
        ], [])
        ->andReturn(['message_id' => '123', 'status' => 'pending']);

    $this->endpoint
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('Test Subject')
        ->html('<img src="cid:logo@example.com">')
        ->attach('logo.png', 'base64encodedimage', 'logo@example.com')
        ->send();
});

test('it sends direct array payloads', function () {
    $payload = [
        'from' => 'sender@example.com',
        'to' => ['recipient@example.com'],
        'subject' => 'Test Subject',
        'text' => 'Plain text content',
    ];

    $this->httpClient
        ->shouldReceive('post')
        ->once()
        ->with('/v1/send', $payload, [])
        ->andReturn(['message_id' => '123', 'status' => 'pending']);

    $response = $this->endpoint->send($payload);

    expect($response->toArray())->toBe(['message_id' => '123', 'status' => 'pending']);
});

test('it sends batch payloads', function () {
    $messages = [
        [
            'from' => 'sender@example.com',
            'to' => ['recipient@example.com'],
            'subject' => 'Test Subject',
        ],
    ];

    $this->httpClient
        ->shouldReceive('post')
        ->once()
        ->with('/v1/send/batch', $messages, [])
        ->andReturn(['data' => [['message_id' => '123', 'status' => 'pending']]]);

    $response = $this->endpoint->sendBatch($messages);

    expect($response->toArray())->toBe(['data' => [['message_id' => '123', 'status' => 'pending']]]);
});

test('it pings the sending API', function () {
    $this->httpClient
        ->shouldReceive('getRaw')
        ->once()
        ->with('/v1/ping', [])
        ->andReturn(' pong');

    expect($this->endpoint->ping())->toBe('pong');
});

test('it handles per email settings', function () {
    $this->httpClient
        ->shouldReceive('post')
        ->once()
        ->with('/v1/send', [
            'from' => 'sender@example.com',
            'to' => ['recipient@example.com'],
            'subject' => 'Test Subject',
            'settings' => ['track_opens' => false, 'track_clicks' => true],
        ], [])
        ->andReturn(['message_id' => '123', 'status' => 'pending']);

    $this->endpoint
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('Test Subject')
        ->settings(['track_opens' => false, 'track_clicks' => true])
        ->send();
});

test('it handles attachment content type', function () {
    $attachment = [
        'filename' => 'invite.ics',
        'content' => 'base64encodedcalendar',
        'content_type' => 'text/calendar; method=REQUEST',
    ];

    $this->httpClient
        ->shouldReceive('post')
        ->once()
        ->with('/v1/send', [
            'from' => 'sender@example.com',
            'to' => ['recipient@example.com'],
            'subject' => 'Test Subject',
            'attachments' => [$attachment],
        ], [])
        ->andReturn(['message_id' => '123', 'status' => 'pending']);

    $this->endpoint
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('Test Subject')
        ->attach('invite.ics', 'base64encodedcalendar', null, 'text/calendar; method=REQUEST')
        ->send();
});

test('it handles custom headers', function () {
    $this->httpClient
        ->shouldReceive('post')
        ->once()
        ->with('/v1/send', [
            'from' => 'sender@example.com',
            'to' => ['recipient@example.com'],
            'subject' => 'Test Subject',
            'headers' => ['X-Custom' => 'Value'],
        ], [])
        ->andReturn(['message_id' => '123', 'status' => 'pending']);

    $this->endpoint
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('Test Subject')
        ->headers(['X-Custom' => 'Value'])
        ->send();
});

test('it supports RFC 5322 email addresses', function () {
    $this->httpClient
        ->shouldReceive('post')
        ->once()
        ->with('/v1/send', [
            'from' => 'John Doe <john@example.com>',
            'to' => ['Jane Doe <jane@example.com>'],
            'subject' => 'Test Subject',
        ], [])
        ->andReturn(['message_id' => '123', 'status' => 'pending']);

    $this->endpoint
        ->from('John Doe <john@example.com>')
        ->to('Jane Doe <jane@example.com>')
        ->subject('Test Subject')
        ->send();
});

test('it handles idempotency key', function () {
    $this->httpClient
        ->shouldReceive('post')
        ->once()
        ->with('/v1/send', [
            'from' => 'sender@example.com',
            'to' => ['recipient@example.com'],
            'subject' => 'Test Subject',
        ], ['Idempotency-Key' => 'unique-key-123'])
        ->andReturn(['message_id' => '123', 'status' => 'pending']);

    $response = $this->endpoint
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('Test Subject')
        ->idempotencyKey('unique-key-123')
        ->send();

    expect($response->toArray())->toBe(['message_id' => '123', 'status' => 'pending']);
});

test('it resets builder state after failed send', function () {
    $this->httpClient
        ->shouldReceive('post')
        ->once()
        ->with('/v1/send', [
            'from' => 'sender@example.com',
            'to' => ['first@example.com'],
            'subject' => 'First',
            'attachments' => [[
                'filename' => 'secret.pdf',
                'content' => 'base64secret',
            ]],
            'metadata' => ['invoice' => '123'],
            'headers' => ['X-Secret' => 'keep-out'],
        ], ['Idempotency-Key' => 'first-key'])
        ->andThrow(new RuntimeException('API unavailable'));

    try {
        $this->endpoint
            ->from('sender@example.com')
            ->to('first@example.com')
            ->subject('First')
            ->attach('secret.pdf', 'base64secret')
            ->metadata(['invoice' => '123'])
            ->headers(['X-Secret' => 'keep-out'])
            ->idempotencyKey('first-key')
            ->send();

        throw new RuntimeException('Expected send to fail.');
    } catch (RuntimeException $exception) {
        expect($exception->getMessage())->toBe('API unavailable');
    }

    $this->httpClient
        ->shouldReceive('post')
        ->once()
        ->with('/v1/send', [
            'from' => 'sender@example.com',
            'to' => ['second@example.com'],
            'subject' => 'Second',
        ], [])
        ->andReturn(['message_id' => '456', 'status' => 'pending']);

    $this->endpoint
        ->from('sender@example.com')
        ->to('second@example.com')
        ->subject('Second')
        ->send();
});

test('it sends without idempotency key when not set', function () {
    $this->httpClient
        ->shouldReceive('post')
        ->once()
        ->with('/v1/send', [
            'from' => 'sender@example.com',
            'to' => ['recipient@example.com'],
            'subject' => 'Test Subject',
        ], [])
        ->andReturn(['message_id' => '123', 'status' => 'pending']);

    $this->endpoint
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('Test Subject')
        ->send();
});

test('it handles metadata', function () {
    $this->httpClient
        ->shouldReceive('post')
        ->once()
        ->with('/v1/send', [
            'from' => 'sender@example.com',
            'to' => ['recipient@example.com'],
            'subject' => 'Test Subject',
            'metadata' => ['foo' => 'bar'],
        ], [])
        ->andReturn(['message_id' => '123', 'status' => 'pending']);

    $this->endpoint
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('Test Subject')
        ->metadata(['foo' => 'bar'])
        ->send();
});

test('it handles tags', function () {
    $this->httpClient
        ->shouldReceive('post')
        ->once()
        ->with('/v1/send', [
            'from' => 'sender@example.com',
            'to' => ['recipient@example.com'],
            'subject' => 'Test Subject',
            'tag' => 'campaign',
        ], [])
        ->andReturn(['message_id' => '123', 'status' => 'pending']);

    $this->endpoint
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('Test Subject')
        ->tag('campaign')
        ->send();
});
