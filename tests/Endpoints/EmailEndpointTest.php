<?php

use Lettermint\Endpoints\EmailEndpoint;
use Lettermint\Client\HttpClient;

beforeEach(function () {
    $this->httpClient = Mockery::mock(HttpClient::class);
    $this->endpoint = new EmailEndpoint($this->httpClient);
});

afterEach(function () {
    Mockery::close();
});

test('it builds email with basic required fields', function () {
    $this->httpClient
        ->shouldReceive('post')
        ->once()
        ->with('/v1/send', [
            'from' => 'sender@example.com',
            'to' => ['recipient@example.com'],
            'subject' => 'Test Subject'
        ])
        ->andReturn(['message_id' => '123', 'status' => 'pending']);

    $response = $this->endpoint
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('Test Subject')
        ->send();

    expect($response)->toBe(['message_id' => '123', 'status' => 'pending']);
});

test('it supports multiple recipients', function () {
    $this->httpClient
        ->shouldReceive('post')
        ->once()
        ->with('/v1/send', [
            'from' => 'sender@example.com',
            'to' => ['recipient1@example.com', 'recipient2@example.com'],
            'subject' => 'Test Subject'
        ])
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
            'text' => 'Plain text content'
        ])
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
            'cc' => ['cc1@example.com', 'cc2@example.com']
        ])
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
            'bcc' => ['bcc1@example.com', 'bcc2@example.com']
        ])
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
            'reply_to' => ['reply@example.com']
        ])
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
        'content' => 'base64encodedcontent'
    ];

    $this->httpClient
        ->shouldReceive('post')
        ->once()
        ->with('/v1/send', [
            'from' => 'sender@example.com',
            'to' => ['recipient@example.com'],
            'subject' => 'Test Subject',
            'attachments' => [$attachment]
        ])
        ->andReturn(['message_id' => '123', 'status' => 'pending']);

    $this->endpoint
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('Test Subject')
        ->attach('test.pdf', 'base64encodedcontent')
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
            'headers' => ['X-Custom' => 'Value']
        ])
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
            'subject' => 'Test Subject'
        ])
        ->andReturn(['message_id' => '123', 'status' => 'pending']);

    $this->endpoint
        ->from('John Doe <john@example.com>')
        ->to('Jane Doe <jane@example.com>')
        ->subject('Test Subject')
        ->send();
});
