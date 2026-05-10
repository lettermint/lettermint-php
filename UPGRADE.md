# Upgrade Guide

## Upgrading from v1 to v2

Version 2 changes the PHP SDK response model for the sending API. The latest released v1 SDK only exposed email sending, so this guide focuses on migrating existing sending integrations.

### 1. Update Composer

```bash
composer require lettermint/lettermint-php:^2.0
```

### 2. Prefer the new email client entry point

The v1 constructor-based style still maps to the email endpoint, but v2 introduces a clearer sending entry point:

```php
$email = Lettermint\Lettermint::email($sendingToken);
```

Before:

```php
$lettermint = new Lettermint\Lettermint($sendingToken);

$response = $lettermint->email
    ->from('sender@example.com')
    ->to('recipient@example.com')
    ->subject('Hello')
    ->send();
```

After:

```php
$email = Lettermint\Lettermint::email($sendingToken);

$response = $email
    ->from('sender@example.com')
    ->to('recipient@example.com')
    ->subject('Hello')
    ->send();
```

Direct payload sending changes the same way:

```php
$response = $email->send([
    'from' => 'sender@example.com',
    'to' => ['recipient@example.com'],
    'subject' => 'Hello',
]);
```

Batch sending:

```php
$response = $email->sendBatch([
    [
        'from' => 'sender@example.com',
        'to' => ['recipient@example.com'],
        'subject' => 'Hello',
    ],
]);
```

### 3. Update response handling

Sending responses are now typed resource objects with IDE autocomplete.

Before:

```php
$response = $lettermint->email->send();

$messageId = $response['message_id'];
$status = $response['status'];
```

After:

```php
$response = $email->send();

$messageId = $response->message_id;
$status = $response->status;
```

Array access is still available:

```php
$messageId = $response['message_id'];
```

Use `toArray()` when passing responses to existing array-based code:

```php
$payload = $response->toArray();
```

Batch responses are also typed:

```php
$response = $email->sendBatch($messages);

$firstMessageId = $response->data[0]->message_id;
```

To keep old array-style processing:

```php
$response = $email->sendBatch($messages)->toArray();

$firstMessageId = $response['data'][0]['message_id'];
```

### 4. Update ping checks

`ping()` now returns the raw API ping response as a string.

Before:

```php
if ($lettermint->email->ping() === 200) {
    // Sending API reachable
}
```

After:

```php
if ($email->ping() === 'pong') {
    // Sending API reachable
}
```

### 5. Search and replace checklist

Search your codebase for:

```text
new Lettermint\Lettermint(
->email
['message_id']
['status']
sendBatch(
ping() === 200
```

Then update response handling to use typed properties or `toArray()`.

### Notes

The main migration risk is code that assumes SDK responses are arrays. Most of that code can be migrated by either using property access or appending `->toArray()` at the SDK boundary.

Version 2 also adds a new full API client via `Lettermint::api($apiToken)`, but this is new functionality rather than a migration requirement from v1.
