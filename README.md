# Lettermint PHP SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lettermint/lettermint-php.svg?style=flat-square)](https://packagist.org/packages/lettermint/lettermint-php)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/lettermint/lettermint-php/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/lettermint/lettermint-php/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/lettermint/lettermint-php.svg?style=flat-square)](https://packagist.org/packages/lettermint/lettermint-php)
[![Join our Discord server](https://img.shields.io/discord/1305510095588819035?logo=discord&logoColor=eee&label=Discord&labelColor=464ce5&color=0D0E28&cacheSeconds=43200)](https://lettermint.co/r/discord)

The official PHP SDK for [Lettermint](https://lettermint.co): send email, manage your team's resources through the Team API, and verify webhooks. For Laravel, use [lettermint/lettermint-laravel](https://github.com/lettermint/lettermint-laravel), which builds on this SDK.

Upgrading from 2.x? Read [UPGRADE.md](UPGRADE.md).

## Requirements

- PHP 8.2 or later
- Guzzle 7.15.2 or later, or Guzzle 8

## Installation

```bash
composer require lettermint/lettermint-php
```

## Quick start

Create a client with a project sending token and send an email:

```php
use Lettermint\Lettermint;

$lettermint = new Lettermint(sendingToken: getenv('LETTERMINT_PROJECT_TOKEN'));

$result = $lettermint->emails->send([
    'from' => 'Acme <hello@acme.com>',
    'to' => ['jane@example.com'],
    'subject' => 'Welcome to Acme',
    'html' => '<p>Thanks for signing up.</p>',
    'text' => 'Thanks for signing up.',
]);

echo $result->message_id, ' ', $result->status; // "…", "pending"
```

## Tokens

Lettermint has two kinds of API tokens:

| Argument | Token | Used by | Sent as |
| --- | --- | --- | --- |
| `sendingToken` | Project sending token (`lm_…`) | `$lettermint->emails` | `x-lettermint-token` header |
| `teamToken` | Team API token (`lm_team_…`) | Every other part (domains, messages, projects, …) | `Authorization: Bearer` header |

Pass one or both as named arguments:

```php
$lettermint = new Lettermint(
    sendingToken: getenv('LETTERMINT_PROJECT_TOKEN'),
    teamToken: getenv('LETTERMINT_TEAM_TOKEN'),
);
```

Each part uses its own token and never falls back to the other one. If the token a method needs is missing, the method throws a `LettermintConfigException` that names the argument (`domains.list needs teamToken; …`) before any request. `$lettermint->ping()` uses the team token when it is set, otherwise the sending token. `messages->reschedule()` and `messages->cancel()` accept either token in the same way.

You can also pass a single token as the first argument. The SDK chooses its type by the format: `lm_team_` followed by letters and digits is a team token, and `lm_` followed by letters and digits is a sending token. Any other value, such as an SSO verification token (`lm_sso_…`) or an empty string, throws a `LettermintConfigException`; pass `sendingToken:` or `teamToken:` explicitly in that case.

```php
$lettermint = new Lettermint(getenv('LETTERMINT_TOKEN'));
$lettermint = new Lettermint(getenv('LETTERMINT_TOKEN'), timeout: 10);
```

Error messages never contain the token.

### Options

| Argument | Default | Description |
| --- | --- | --- |
| `sendingToken` | | Project sending token. |
| `teamToken` | | Team API token. |
| `baseUrl` | `https://api.lettermint.co/v1` | API base URL, including its path. |
| `timeout` | `30` | Request timeout in seconds. It covers connecting, sending and reading the whole response. |
| `httpClient` | a new `GuzzleHttp\Client` | A Guzzle client, for example with a proxy or a test handler. The SDK still disables redirects and sets the timeout on every request. |

The client holds no per-request state, so create it once and share it, also in long-running workers such as Octane, Horizon or Symfony Messenger.

## Sending email

### The email builder

`$lettermint->emails->compose()` returns an immutable builder. Every setter returns a new builder and leaves the original unchanged, so you can keep a base builder and reuse it:

```php
$welcome = $lettermint->emails->compose()
    ->from('Acme <hello@acme.com>')
    ->subject('Welcome to Acme')
    ->tags([['name' => 'campaign', 'value' => 'welcome']]);

$welcome->to('jane@example.com')->html('<p>Hi Jane</p>')->send();
$welcome->to('john@example.com')->html('<p>Hi John</p>')->send();
```

When you build an email over several statements, keep the returned builder:

```php
$email = $lettermint->emails->compose()->from('hello@acme.com')->to($user->email)->subject('Your invoice');
if ($user->accountant !== null) {
    $email = $email->cc($user->accountant);
}
$email->html($invoiceHtml)->send();
```

Builder methods:

| Method | Description |
| --- | --- |
| `from($address)` | Sender, for example `Acme <hello@acme.com>`. |
| `to(...$addresses)`, `cc(...)`, `bcc(...)`, `replyTo(...)` | Replace the recipient list. |
| `subject($text)` | Subject line. |
| `html($html)`, `text($text)` | Bodies. `null` removes one. |
| `headers($headers)` | Custom email headers. |
| `metadata($metadata)` | Data stored with the message, not added as headers. |
| `tags([['name' => …, 'value' => …]])`, `tag($name)` | Name/value tags, and the legacy single tag (`null` removes it). |
| `route($slug)` | The route to send through. |
| `scheduledAt($when)` | Delivery time: a `DateTimeInterface`, ISO 8601, or English such as `tomorrow 9am`. `null` removes it. |
| `settings(['track_opens' => …, 'track_clicks' => …, 'tls' => …])` | Per-email settings that override the route. |
| `sandboxResult($result)` | The result a Sandbox project simulates. |
| `attach($attachment)` | Adds an `Attachment`, or an array in the API's format. |
| `send(idempotencyKey: …)` | Sends the email. The builder can be sent again. |
| `build()` | Returns the message in the API's format. |

`$lettermint->emails->compose($message)` starts a builder from an existing message array.

### Arrays

`$lettermint->emails->send()` takes the message in the API's format (`reply_to`, `scheduled_at`, `sandbox_result`, …). PHPStan and IDEs know the shape as `SendMailRequest`:

```php
$lettermint->emails->send([
    'from' => 'Acme <hello@acme.com>',
    'to' => ['jane@example.com'],
    'reply_to' => ['support@acme.com'],
    'subject' => 'Your order has shipped',
    'html' => $html,
    'metadata' => ['order_id' => '1234'],
]);
```

### Batch sending

Send up to 500 emails in one request. The list may mix arrays and builders. The result is a list with one `SendMailResponse` per email:

```php
$results = $lettermint->emails->sendBatch([
    ['from' => 'hello@acme.com', 'to' => ['jane@example.com'], 'subject' => 'Hi Jane', 'text' => 'Hello'],
    $welcome->to('john@example.com')->html('<p>Hi John</p>'),
]);
```

### Idempotency

Pass an idempotency key to make retries safe. The API processes a key once, so a retry with the same key does not send the email again. The key applies only to the call it is passed to.

```php
$lettermint->emails->send($message, idempotencyKey: "order-{$order->id}-confirmation");
$builder->send(idempotencyKey: 'welcome-jane');
$lettermint->emails->sendBatch($messages, idempotencyKey: 'newsletter-2026-10');
```

The SDK never retries on its own.

### Scheduling

```php
$result = $lettermint->emails->compose()
    ->from('hello@acme.com')
    ->to('jane@example.com')
    ->subject('Your trial ends tomorrow')
    ->text('…')
    ->scheduledAt(new DateTimeImmutable('+1 day'))
    ->send();

if ($result->status === \Lettermint\Types\MessageStatus::SCHEDULED) {
    echo $result->scheduled_at;
}

$lettermint->messages->reschedule($result->message_id, ['scheduled_at' => '2026-10-20T09:00:00Z']);
$lettermint->messages->cancel($result->message_id);
```

### Sandbox

In a Sandbox project, nothing is delivered. Choose the simulated result per email:

```php
$result = $lettermint->emails->compose()
    ->from('hello@acme.com')
    ->to('jane@example.com')
    ->subject('Test')
    ->text('Test')
    ->sandboxResult('hard_bounced')
    ->send();

var_dump($result->sandbox, $result->sandbox_result); // true, "hard_bounced"
```

### Tags

`tags()` accepts up to 20 case-sensitive name/value tags (19 when the legacy `tag()` is also set). Names match `^[A-Za-z0-9_-]{1,32}$`, may not start with `__lettermint` and must be unique. Values match `^[A-Za-z0-9_-]{1,64}$`. The SDK checks this before the request and throws `LettermintValidationException`. Because builders are immutable, a rejected tag leaves the builder unchanged.

### Attachments

`Attachment` takes raw bytes and base64-encodes them for you:

```php
use Lettermint\Attachment;

$lettermint->emails->compose()
    ->from('billing@acme.com')
    ->to('jane@example.com')
    ->subject('Your invoice')
    ->html('<img src="cid:logo"> Your invoice is attached.')
    ->attach(Attachment::fromPath('/path/to/invoice.pdf', contentType: 'application/pdf'))
    ->attach(new Attachment('report.csv', $csvBytes, 'text/csv'))
    ->attach(Attachment::fromBase64('logo.png', $logoBase64, contentId: 'logo'))
    ->send();
```

In a message array, attachments use the API's format with base64 content: `['filename' => 'invoice.pdf', 'content' => base64_encode($pdf)]`, or `Attachment::fromPath(...)->toArray()`.

`$lettermint->blockedFileTypes()` lists the extensions and MIME types the API rejects.

## Team API

With a team token, the client manages domains, messages, projects, routes, statistics, suppressions, the team and webhooks:

```php
$lettermint = new Lettermint(teamToken: getenv('LETTERMINT_TEAM_TOKEN'));

$domain = $lettermint->domains->create(['domain' => 'acme.com']);
$lettermint->domains->verifyDnsRecords($domain->id);

$project = $lettermint->projects->create(['name' => 'Production']);
echo $project->api_token; // the new project's sending token, shown once

$stats = $lettermint->stats->retrieve(['from' => '2026-10-01', 'to' => '2026-10-31']);
$html = $lettermint->messages->html('message-id');
```

| Property | Methods |
| --- | --- |
| `domains` | `list`, `iterate`, `create`, `retrieve`, `delete`, `verifyDnsRecords`, `verifyDnsRecord`, `updateProjects` |
| `messages` | `list`, `iterate`, `retrieve`, `events`, `iterateEvents`, `source`, `html`, `text`, `reschedule`, `cancel`, `process` |
| `projects` | `list`, `iterate`, `create`, `retrieve`, `update`, `delete`, `rotateToken` |
| `projects->reportForwarding` | `retrieve`, `update`, `delete`, `verify`, `resendCode` |
| `routes` | `list($projectId)`, `iterate($projectId)`, `create($projectId, …)`, `retrieve`, `update`, `delete`, `verifyInboundDomain` |
| `stats` | `retrieve` |
| `suppressions` | `list`, `iterate`, `create`, `delete` |
| `team` | `retrieve`, `update`, `usage`, `roles` |
| `team->members` | `list`, `iterate`, `retrieve`, `updateAssignment` |
| `webhooks` | `list`, `iterate`, `create`, `retrieve`, `update`, `delete`, `test`, `regenerateSecret` |
| `webhooks->deliveries` | `list($webhookId)`, `iterate($webhookId)`, `retrieve($webhookId, $deliveryId)` |
| (root) | `ping`, `analytics`, `blockedFileTypes` |

### Responses

Responses are read-only objects from `Lettermint\Types`, such as `DomainData`, `MessageData` and `SendMailResponse`. Their fields have PHPStan types and IDE completion:

```php
$message = $lettermint->messages->retrieve('message-id');
echo $message->subject;
foreach ($message->to ?? [] as $recipient) {
    echo $recipient->email;
}
$message['subject'];   // array access works too
$message->toArray();   // the JSON as the API sent it
$message->has('spam_score'); // whether an optional field is present
```

Fields and enum values that this SDK version does not know yet pass through: read them with `$object['field']` or `$object->get('field')`. Enums are open: a status the API adds later is returned as it is. The enum classes, such as `MessageStatus` and `WebhookEvent`, have one constant per known value; give `match` expressions a `default` arm.

### Query parameters and pagination

Query parameters are nested arrays. The SDK sends them in the API's bracket syntax (`page[size]=30&filter[status]=verified&sort=-created_at`):

```php
$page = $lettermint->domains->list([
    'page' => ['size' => 30],
    'filter' => ['status' => 'verified'],
    'sort' => ['-created_at'],
]);

echo count($page), ' ', $page->next_cursor;
$next = $lettermint->domains->list(['page' => ['size' => 30, 'cursor' => $page->next_cursor]]);
```

A page (`CursorPage`) has `data`, `next_cursor` and `hasMore()`, and you can `foreach` over it. Every list also has an `iterate()` method, a generator that follows `next_cursor` until the last page and requests a page only when you get to it:

```php
foreach ($lettermint->messages->iterate(['filter' => ['status' => 'hard_bounced']]) as $message) {
    echo $message->id, ' ', $message->subject, PHP_EOL;
}
```

## Errors

Every exception the SDK throws extends `Lettermint\Exceptions\LettermintException`:

| Class | When | Properties |
| --- | --- | --- |
| `ApiException` | Any 4xx or 5xx JSON (or empty) response | `status`, `errorCode`, `getMessage()`, `details`, `body` |
| `AuthenticationException` | 401 | |
| `PermissionException` | 403 | |
| `NotFoundException` | 404 | |
| `ConflictException` | 409 | |
| `ValidationException` | 422 | `errors` (field errors) |
| `RateLimitException` | 429 | `retryAfter` (seconds) |
| `ServerException` | 5xx | |
| `TimeoutException` | No complete response within the timeout | `timeout` |
| `ConnectionException` | The request failed (DNS, TLS, refused, reset) | |
| `UnexpectedResponseException` | An empty or non-JSON body where JSON was expected, or an error page such as a proxy's HTML 502 | `status`, `bodyExcerpt` |
| `RedirectException` | A 3xx response. Redirects are never followed, so tokens never go elsewhere. | `status` |
| `LettermintConfigException` | A missing or unrecognised token, an invalid option or ID | |
| `LettermintValidationException` | The SDK rejected the request before sending it, such as invalid tags | `field` |
| `WebhookVerificationException` | A webhook delivery is not genuine | `reason` |

The `ApiException` subclasses extend `ApiException`, and `getCode()` returns the HTTP status. `errorCode` and the message come from the API's error body (`{"error": {"code", "message", "details"}}` or `{"message", "errors"}`).

```php
use Lettermint\Exceptions\ApiException;
use Lettermint\Exceptions\RateLimitException;
use Lettermint\Exceptions\TimeoutException;
use Lettermint\Exceptions\ValidationException;

try {
    $lettermint->emails->send($message, idempotencyKey: $key);
} catch (ValidationException $e) {
    report($e->getMessage(), $e->errors);
} catch (RateLimitException $e) {
    sleep($e->retryAfter ?? 1); // then retry with the same idempotency key
} catch (TimeoutException $e) {
    // The outcome is unknown. Retry with the same idempotency key.
} catch (ApiException $e) {
    report($e->status, $e->errorCode, $e->getMessage());
}
```

Exceptions never contain request headers or tokens. The SDK keeps tokens in `SensitiveParameterValue` objects and marks token arguments with `#[\SensitiveParameter]`, so `var_dump()`, `print_r()`, `var_export()`, `json_encode()` and stack traces show them as redacted or not at all. Clients, builders and webhook verifiers cannot be serialized.

## Webhooks

Verify each webhook delivery before you trust it. Use the webhook's signing secret (`whsec_…`), not an API token, and pass the **raw** request body: the signature covers the exact bytes, so decoding and re-encoding the JSON breaks it.

```php
use Lettermint\Exceptions\WebhookVerificationException;
use Lettermint\Webhook;

$webhook = new Webhook(getenv('LETTERMINT_WEBHOOK_SECRET'));

try {
    $payload = $webhook->verify($request->getContent(), $request->headers);
} catch (WebhookVerificationException $e) {
    return new Response('Invalid signature', 400);
}

echo $payload->event; // "message.delivered"
$payload->data;      // the event data
```

`verify($rawBody, $headers)` takes the headers as an array (`['X-Lettermint-Signature' => '…']`, or lists of values as from `$request->headers->all()`), a Symfony or Laravel `HeaderBag`, or a PSR-7 message. Header names are case-insensitive. It requires `X-Lettermint-Signature` and `X-Lettermint-Delivery`, checks the HMAC-SHA256 signature with a constant-time comparison, checks that the delivery timestamp equals the signed one and is within the tolerance, and returns a `WebhookPayload` (`id`, `event`, `timestamp`, `data`, and `toArray()` for the whole payload). Otherwise it throws `WebhookVerificationException` with a `reason`: `signature_header_missing`, `signature_header_malformed`, `delivery_header_missing`, `delivery_timestamp_mismatch`, `timestamp_out_of_tolerance`, `signature_mismatch`, `body_invalid` or `payload_invalid` (constants on the exception class).

With PSR-7:

```php
$payload = $webhook->verify((string) $request->getBody(), $request);
```

The default tolerance is 300 seconds in either direction. Change it with `new Webhook($secret, tolerance: 60)`. `0` accepts only the current second; it does not disable the check. A valid signature does not prevent a repeated delivery within the tolerance, so track `$payload->id` if you must not process an event twice. If the headers are not at hand, call `$webhook->verifySignature($rawBody, $signatureHeader, $deliveryHeader)`.

## Development

```bash
composer install
composer test
composer analyse
composer format
```

`src/Types/` is generated by the private [SDK generator](https://github.com/lettermint/sdk-generator). Do not edit it by hand. With a checkout of the generator, `composer generate` regenerates the files and `composer generate:check` verifies them; set `LETTERMINT_SDK_GENERATOR` to the checkout (default `../sdk-generator`). Without the generator, as in CI, `composer generate:check` only verifies the generated headers.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Bjarn Bronsveld](https://github.com/bjarn)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
