# Lettermint PHP SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lettermint/lettermint-php.svg?style=flat-square)](https://packagist.org/packages/lettermint/lettermint-php)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/lettermint/lettermint-php/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/lettermint/lettermint-php/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/lettermint/lettermint-php/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/lettermint/lettermint-php/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/lettermint/lettermint-php.svg?style=flat-square)](https://packagist.org/packages/lettermint/lettermint-php)

Integrate Lettermint in your PHP project.

## Requirements

- PHP 8.2 or higher
- Composer

## Installation

You can install the package via composer:

```bash
composer require lettermint/lettermint-php
```

## Usage

Initialize the Lettermint client with your API token:

```php
$lettermint = new Lettermint\Lettermint('your-api-token');
```

### Sending Emails

The SDK provides a fluent interface for composing and sending emails:

```php
$response = $lettermint->email
                       ->from('sender@example.com')
                       ->to('recipient@example.com')
                       ->subject('Hello from Lettermint!')
                       ->text('Hello! This is a test email.')
                       ->send();

```

The SDK supports various email features:

```php
$lettermint->email
    ->from('John Doe <john@example.com>')
    ->to('recipient1@example.com', 'recipient2@example.com')
    ->cc('cc@example.com')
    ->bcc('bcc@example.com')
    ->replyTo('reply@example.com')
    ->subject('Hello world!')
    ->html('<h1>Hello!</h1>')
    ->text('Hello!')
    ->headers(['X-Custom-Header' => 'Value'])
    ->attach('document.pdf', base64_encode($fileContent))
    ->attach('logo.png', base64_encode($logoContent), 'logo@example.com')
    ->route('my-route-id')
    ->idempotencyKey('unique-request-id-123')
    ->send();
```

#### Inline Attachments

You can embed images and other content in your HTML emails using content IDs:

```php
$lettermint->email
    ->from('sender@example.com')
    ->to('recipient@example.com')
    ->subject('Email with inline image')
    ->html('<p>Here is an image: <img src="cid:logo@example.com"></p>')
    ->attach('logo.png', base64_encode($imageContent), 'logo@example.com')
    ->send();
```

### Idempotency

To ensure that duplicate requests are not processed, you can use an idempotency key:

```php
$response = $lettermint->email
                       ->from('sender@example.com')
                       ->to('recipient@example.com')
                       ->subject('Hello from Lettermint!')
                       ->text('Hello! This is a test email.')
                       ->idempotencyKey('unique-request-id-123')
                       ->send();
```

The idempotency key should be a unique string that you generate for each unique email you want to send. If you make the
same request with the same idempotency key, the API will return the same response without sending a duplicate email.

For more information, refer to the [documentation](https://docs.lettermint.co/platform/emails/idempotency).

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Bjarn Bronsveld](https://github.com/bjarn)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
