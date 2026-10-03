# Upgrade guide

- [Upgrade from 2.x to 3.0](#upgrade-from-2x-to-30)
- [Upgrade from 1.x to 2.0](#upgrading-from-v1-to-v2)

# Upgrade from 2.x to 3.0

2.x no longer receives updates, including fixes. Upgrade to 3.0 to keep getting them.

3.0 is a new major version. The main reason is safety: in 2.x, `$lettermint->email` was one mutable builder cached on the client, and `Lettermint::email()` returned one as well. An email abandoned halfway (for example because `tags()` threw) leaked its recipients, bodies, attachments and `Idempotency-Key` into the next send, and long-running workers (Octane, Horizon, queue workers, Symfony Messenger) kept that builder alive between jobs. 3.0 stores nothing about a message on the client.

## Highlights

- One client: `new Lettermint(sendingToken: …, teamToken: …)`, or `new Lettermint($token)`. It replaces `Lettermint::email()`, `Lettermint::api()` and `$lettermint->email`.
- Sending is stateless: `emails->send($message, idempotencyKey: …)`, `emails->sendBatch($messages, idempotencyKey: …)` and an immutable `emails->compose()` builder. The `Idempotency-Key` is a per-call argument.
- Each part uses its own token: `emails` uses the sending token, the Team API uses the team token. The SDK never falls back to the other token.
- Typed exceptions for every outcome under `Lettermint\Exceptions`, all extending `LettermintException`, instead of `\Exception` with code 0. `ApiException::getCode()` is the HTTP status.
- Redirects are never followed, so tokens are never sent to another host. The timeout covers the whole request.
- Empty and HTML responses raise `UnexpectedResponseException`; a 204 (report forwarding `delete()`) no longer throws.
- Tokens and webhook secrets never appear in `var_dump()`, `print_r()`, `var_export()`, `json_encode()`, serialized data or stack traces.
- The path of `baseUrl` is kept (2.x dropped it), and empty, `.` and `..` IDs are rejected before a request.
- Response classes are generated from the current API specification and use its names (see [Type names](#type-names)). Enums are open.
- Query parameters are nested arrays (`['page' => ['size' => 30]]`), and every list has an `iterate()` generator that follows `next_cursor`.
- `Webhook::verify($rawBody, $headers)` requires both signature headers and returns a `WebhookPayload`; failures have a `reason`.

## Requirements

- PHP 8.2 or later (unchanged).
- Guzzle 7.15.2 or later, or Guzzle 8 (unchanged).

## Upgrade with a coding agent

You can let a coding agent (Claude Code, Codex, Cursor, Copilot, …) do the upgrade. Copy this instruction into the agent from your project's root, then review its changes:

````text
Upgrade this project from the `lettermint/lettermint-php` PHP SDK 2.x to 3.0.

1. Run `composer require lettermint/lettermint-php:^3.0`. If the project uses `lettermint/lettermint-laravel`, upgrade that package to the release that supports SDK 3.0 instead, and follow its upgrade guide too. 3.0 needs PHP 8.2 or newer: check `composer.json`, CI workflows and Dockerfiles, and report anything older.
2. Read the upgrade guide before changing code: `vendor/lettermint/lettermint-php/UPGRADE.md`, or https://github.com/lettermint/lettermint-php/blob/main/UPGRADE.md. Treat it as the source of truth and don't guess APIs; when unsure, read the classes in `vendor/lettermint/lettermint-php/src/` (request shapes are in `src/Types/ApiTypes.php`).
3. Find every use of the SDK: `Lettermint\` imports, `new Lettermint(`, `Lettermint::email(`, `Lettermint::api(`, `->email->`, `ApiClient`, `EmailEndpoint`, `->idempotencyKey(`, `->attach(`, `->sendBatch(`, `MessageTag`, `Webhook`, `verifyHeaders(`, `Webhook::verifySignature(`, the 2.x exception classes, `Lettermint\Objects\`, `Lettermint\Responses\` and `@phpstan-import-type … from ApiTypes`.
4. Rewrite each use following the guide's before/after examples:
   - Create one client with `new Lettermint(sendingToken: …)`, adding `teamToken:` only where the Team API is used. Keep the project's existing environment variable or config names.
   - Replace fluent chains on `$lettermint->email` or `Lettermint::email()` with `$lettermint->emails->send([...])`, or with `$lettermint->emails->compose()` per email, assigning the result of every setter. Never keep a builder in a property or static that outlives one email unless it is used as an immutable template.
   - Move idempotency keys into `send(idempotencyKey: …)` / `sendBatch(…, idempotencyKey: …)`. Attachments become `Attachment` objects (`new Attachment($filename, $rawBytes)`, `Attachment::fromBase64(…)`, `Attachment::fromPath(…)`). Tags become `['name' => …, 'value' => …]` arrays.
   - Team API: use the same client's properties, nested query arrays instead of `'page[size]'`-style keys, and the renamed methods from the guide.
   - Errors: catch the 3.0 exception classes from `Lettermint\Exceptions` instead of `\Exception`, and read `status`, `errorCode`, `errors` and `retryAfter` from them.
   - Webhooks: `(new Webhook($secret))->verify($rawBody, $headers)`. Keep passing the raw request body, keep the secret's `whsec_` prefix, and make sure the `X-Lettermint-Signature` and `X-Lettermint-Delivery` headers reach the handler.
   - Rename types using the guide's type-name table. Watch out for `MessageResponse`, which now means a `{message}` confirmation, not a message.
5. Run the project's static analysis (PHPStan/Psalm), code style and tests, and fix every error. Don't send real email or call the live API while testing.
6. Finish with a summary: the files you changed, anything you could not migrate with certainty, and behaviour changes I should review.

Never print, log or commit API tokens or webhook secrets.
````

## Create the client

`Lettermint::email()`, `Lettermint::api()`, `$lettermint->email`, `Lettermint\Client\ApiClient` and `Lettermint\Client\HttpClient` are removed. The constructor takes named arguments.

```php
// 2.x
$lettermint = new Lettermint\Lettermint($sendingToken);                 // $lettermint->email
$email = Lettermint\Lettermint::email($sendingToken, timeout: 10);
$api = Lettermint\Lettermint::api($teamToken, 'https://api.lettermint.co/v1');

// 3.0
use Lettermint\Lettermint;

$lettermint = new Lettermint(
    sendingToken: getenv('LETTERMINT_PROJECT_TOKEN'), // for $lettermint->emails
    teamToken: getenv('LETTERMINT_TEAM_TOKEN'),       // for the Team API
    timeout: 10,
);
```

Pass one token or both. With only one token, calling a part that needs the other throws `LettermintConfigException` (for example `domains.list needs teamToken; …`) before any request. An empty string is not "no token": it throws `LettermintConfigException`, so pass `null` for a token you do not have.

You can also pass a single token string. The SDK chooses the token type by its format:

```php
$lettermint = new Lettermint('lm_team_...'); // team token
$lettermint = new Lettermint('lm_...');      // project sending token
$lettermint = new Lettermint($token, timeout: 10);
```

Any other format (SSO tokens, OAuth tokens, an empty string) throws `LettermintConfigException`. Use `sendingToken:` or `teamToken:` for those.

| 2.x | 3.0 |
| --- | --- |
| `new Lettermint($token, $baseUrl, $timeout)` | `new Lettermint($token, baseUrl: $baseUrl, timeout: $timeout)` (named arguments) |
| `$baseUrl` with a path (`https://proxy.example/lettermint/v1`) | The path is kept; 2.x sent requests to `/v1/...` on the host and dropped it |
| `int $timeout = 15` (seconds) | `float $timeout = 30.0` (seconds), covering connecting, sending and reading the whole response |
| — | `httpClient:` a `GuzzleHttp\ClientInterface`, for proxies or tests. Redirects stay disabled and the timeout is set per request. |

## Send an email

The 2.x builder lived on the client (or on the `EmailEndpoint` from `Lettermint::email()`) and was reset only after `send()`. In 3.0, `$lettermint->emails->compose()` returns an immutable builder: each setter returns a new builder and leaves the old one unchanged. Chaining works as before. If you built an email over several statements, assign the result of each setter.

```php
// 2.x
$lettermint->email
    ->from('Acme <hello@acme.com>')
    ->to('jane@example.com')
    ->subject('Welcome')
    ->html('<p>Hi Jane</p>')
    ->idempotencyKey('welcome-jane')
    ->send();

// 3.0: builder
$lettermint->emails->compose()
    ->from('Acme <hello@acme.com>')
    ->to('jane@example.com')
    ->subject('Welcome')
    ->html('<p>Hi Jane</p>')
    ->send(idempotencyKey: 'welcome-jane');

// 3.0: array (API field names)
$lettermint->emails->send(
    ['from' => 'Acme <hello@acme.com>', 'to' => ['jane@example.com'], 'subject' => 'Welcome', 'html' => '<p>Hi Jane</p>'],
    idempotencyKey: 'welcome-jane',
);
```

```php
// 2.x: statements changed the shared builder
$email = Lettermint\Lettermint::email($token);
$email->from('hello@acme.com');
$email->to('jane@example.com');
if ($copy) {
    $email->cc('team@acme.com');
}
$email->subject('Hi')->send();

// 3.0: keep the returned builder
$draft = $lettermint->emails->compose()->from('hello@acme.com')->to('jane@example.com');
if ($copy) {
    $draft = $draft->cc('team@acme.com');
}
$draft->subject('Hi')->send();
```

```php
// 2.x: send an array on the endpoint, key set on the builder first
$email->idempotencyKey($key);
$email->send($payload);

// 3.0
$lettermint->emails->send($payload, idempotencyKey: $key);
```

A base builder can now be kept and shared safely:

```php
$welcome = $lettermint->emails->compose()->from('Acme <hello@acme.com>')->subject('Welcome');
$welcome->to('jane@example.com')->html($janeHtml)->send();
$welcome->to('john@example.com')->html($johnHtml)->send(idempotencyKey: 'welcome-john');
```

### Changed builder methods

| 2.x | 3.0 |
| --- | --- |
| `$lettermint->email->from($x)` and the other setters change the shared builder and return it | Return a new builder: `$lettermint->emails->compose()->from($x)` |
| `->idempotencyKey($key)->send()` | `->send(idempotencyKey: $key)` |
| `->attach($filename, $base64, $contentId, $contentType)` | `->attach(Attachment::fromBase64($filename, $base64, $contentType, $contentId))` (note the order: content type before content ID), `->attach(new Attachment($filename, $rawBytes))` or `->attach(Attachment::fromPath($path))` |
| `->html(null)`, `->text(null)`, `->tag(null)` sent `null` | `null` removes the field; so does `scheduledAt(null)` |
| `->scheduledAt(string)` | `->scheduledAt(string\|DateTimeInterface\|null)`; dates are sent as ISO 8601 in UTC |
| `->tags([new MessageTag('a', 'b')])` | `->tags([['name' => 'a', 'value' => 'b']])`; `Lettermint\Objects\MessageTag` is removed |
| `->tags()`/`->tag()` threw `InvalidArgumentException` | Throw `LettermintValidationException` (with `field`), before any request, and leave the builder unchanged |
| `send(?array $payload)` sent the array or the builder | `$builder->send(idempotencyKey: …)` sends the builder; `$lettermint->emails->send($array)` sends an array |
| — | `$builder->build()` returns the message in the API's format |

Unchanged setters: `to`, `cc`, `bcc`, `replyTo` (each replaces its list), `subject`, `headers`, `metadata`, `route`, `settings`, `tag`, `sandboxResult`.

Attachment content was base64 in 2.x. In 3.0, `Attachment` takes raw bytes, and `Attachment::fromBase64()` takes base64:

```php
// 2.x
$email->attach('invoice.pdf', base64_encode($pdf), null, 'application/pdf');
$email->attach('logo.png', $logoBase64, 'logo');

// 3.0
use Lettermint\Attachment;

$builder
    ->attach(new Attachment('invoice.pdf', $pdf, 'application/pdf'))
    ->attach(Attachment::fromBase64('logo.png', $logoBase64, contentId: 'logo'));
```

In message arrays, attachments keep the API's format with base64 content (`['filename' => …, 'content' => base64_encode($bytes)]`), or use `Attachment::fromPath(…)->toArray()`.

## Batch sending and ping

```php
// 2.x
$email->idempotencyKey('batch-1')->sendBatch([$message1, $message2]); // SendBatchMailResponse, ->data[0]
$email->ping();
$api->ping();

// 3.0
$results = $lettermint->emails->sendBatch([$message1, $message2], idempotencyKey: 'batch-1'); // list<SendMailResponse>, $results[0]
$lettermint->emails->sendBatch([$builder1, $builder2]); // builders work too
$lettermint->emails->ping(); // sending token
$lettermint->ping();         // team token if configured, otherwise the sending token
```

## Responses

Responses are still read-only objects with typed properties, array access and `toArray()`, now from `Lettermint\Types` (see [Type names](#type-names)). Changes:

- Optional fields that are absent read as `null`; `$object->has('field')` tells an absent field from a `null` one.
- Fields the SDK does not know pass through: read them with `$object['field']` or `$object->get('field')`.
- Enums are open: an unknown status is returned as it is. Enum classes such as `Lettermint\Types\MessageStatus` list the known values as constants (`MessageStatus::DELIVERED`).
- `getAttribute($name)` and `getAttributes()` are removed. Use `$object->name` or `$object->get($name)`, and `$object->toArray()`.
- Lists are `CursorPage` objects: `$page->data`, `$page->next_cursor`, `$page->hasMore()`, `count($page)`, and `foreach ($page as $item)`.
- `projects->reportForwarding->delete()` returns nothing; in 2.x the 204 response made it throw.
- The text endpoints (`messages->source()`, `html()`, `text()`, `ping()`) return strings, as before.

## Team API

The sub-clients move from `Lettermint::api($token)->x` to `$lettermint->x`. Query parameters are nested arrays instead of bracketed keys. Every list also has an `iterate()` generator that follows `next_cursor`.

```php
// 2.x
$api = Lettermint\Lettermint::api($token);
$page = $api->domains->list(['page[size]' => 10, 'filter[status]' => 'verified']);

// 3.0
$page = $lettermint->domains->list(['page' => ['size' => 10], 'filter' => ['status' => 'verified']]);
foreach ($lettermint->domains->iterate(['filter' => ['status' => 'verified']]) as $domain) {
    echo $domain->domain;
}
```

| 2.x (`$api = Lettermint::api($token)`) | 3.0 (`$lettermint = new Lettermint(teamToken: $token)`) |
| --- | --- |
| `$api->ping()` | `$lettermint->ping()` |
| `$api->blockedFileTypes()` | `$lettermint->blockedFileTypes()` |
| `$api->analytics($data)` | `$lettermint->analytics($query)` |
| `$api->domains->list($query)` | `$lettermint->domains->list($query)`, `$lettermint->domains->iterate($query)` |
| `$api->domains->create($data)` | `$lettermint->domains->create($body)` |
| `$api->domains->retrieve($id, $query)` | `$lettermint->domains->retrieve($id, $query)` (`['include' => ['dnsRecords']]`) |
| `$api->domains->delete($id)` | `$lettermint->domains->delete($id)` |
| `$api->domains->verifyDnsRecords($id)` | `$lettermint->domains->verifyDnsRecords($id)` |
| `$api->domains->verifyDnsRecord($id, $recordId)` | `$lettermint->domains->verifyDnsRecord($id, $recordId)` |
| `$api->domains->updateProjects($id, $data)` | `$lettermint->domains->updateProjects($id, $body)` |
| `$api->messages->list($query)` | `$lettermint->messages->list($query)`, `$lettermint->messages->iterate($query)` |
| `$api->messages->retrieve($id)` | `$lettermint->messages->retrieve($id)` |
| `$api->messages->events($id, $query)` | `$lettermint->messages->events($id, $query)`, `$lettermint->messages->iterateEvents($id, $query)` |
| `$api->messages->source($id)` / `html($id)` / `text($id)` | unchanged, on `$lettermint->messages` |
| `$api->messages->reschedule($id, $data)` | `$lettermint->messages->reschedule($id, $body)` |
| `$api->messages->cancel($id)` | `$lettermint->messages->cancel($id)` |
| `$api->messages->process($id)` | `$lettermint->messages->process($id, idempotencyKey: …)` |
| `$api->projects->list($query)` | `$lettermint->projects->list($query)`, `$lettermint->projects->iterate($query)` |
| `$api->projects->create($data)` | `$lettermint->projects->create($body)` |
| `$api->projects->retrieve($id, $query)` | `$lettermint->projects->retrieve($id, $query)` |
| `$api->projects->update($id, $data)` | `$lettermint->projects->update($id, $body)` |
| `$api->projects->delete($id)` | `$lettermint->projects->delete($id)` |
| `$api->projects->rotateToken($id)` | `$lettermint->projects->rotateToken($id)` (deprecated by the API) |
| `$api->projects->routes($projectId, $query)` | `$lettermint->routes->list($projectId, $query)`, `$lettermint->routes->iterate($projectId, $query)` |
| `$api->projects->createRoute($projectId, $data)` | `$lettermint->routes->create($projectId, $body)` |
| `$api->projects->retrieveReportForwarding($id)` | `$lettermint->projects->reportForwarding->retrieve($id)` |
| `$api->projects->updateReportForwarding($id, $data)` | `$lettermint->projects->reportForwarding->update($id, $body)` |
| `$api->projects->deleteReportForwarding($id)` | `$lettermint->projects->reportForwarding->delete($id)` |
| `$api->projects->verifyReportForwarding($id, $data)` | `$lettermint->projects->reportForwarding->verify($id, $body)` |
| `$api->projects->resendReportForwardingCode($id)` | `$lettermint->projects->reportForwarding->resendCode($id)` |
| `$api->routes->retrieve($id, $query)` | `$lettermint->routes->retrieve($id, $query)` |
| `$api->routes->update($id, $data)` | `$lettermint->routes->update($id, $body)` |
| `$api->routes->delete($id)` | `$lettermint->routes->delete($id)` |
| `$api->routes->verifyInboundDomain($id)` | `$lettermint->routes->verifyInboundDomain($id)` |
| `$api->stats->retrieve($query)` | `$lettermint->stats->retrieve(['from' => …, 'to' => …, 'project_id' => …, 'include_machine' => …])` |
| `$api->suppressions->list($query)` | `$lettermint->suppressions->list($query)`, `$lettermint->suppressions->iterate($query)` |
| `$api->suppressions->create($data)` | `$lettermint->suppressions->create($body)` |
| `$api->suppressions->delete($id)` | `$lettermint->suppressions->delete($id)` |
| `$api->team->retrieve($query)` | `$lettermint->team->retrieve($query)` (`['include' => ['features']]`) |
| `$api->team->update($data)` | `$lettermint->team->update($body)` |
| `$api->team->usage()` | `$lettermint->team->usage()` |
| `$api->team->roles()` | `$lettermint->team->roles()` |
| `$api->team->members($query)` | `$lettermint->team->members->list($query)`, `$lettermint->team->members->iterate($query)` |
| `$api->team->member($userId)` | `$lettermint->team->members->retrieve($userId)` |
| `$api->team->updateMemberAssignment($userId, $data)` | `$lettermint->team->members->updateAssignment($userId, $body)` |
| `$api->webhooks->list($query)` | `$lettermint->webhooks->list($query)`, `$lettermint->webhooks->iterate($query)` |
| `$api->webhooks->create($data)` | `$lettermint->webhooks->create($body)` |
| `$api->webhooks->retrieve($id)` | `$lettermint->webhooks->retrieve($id)` |
| `$api->webhooks->update($id, $data)` | `$lettermint->webhooks->update($id, $body)` |
| `$api->webhooks->delete($id)` | `$lettermint->webhooks->delete($id)` |
| `$api->webhooks->test($id)` | `$lettermint->webhooks->test($id)` |
| `$api->webhooks->regenerateSecret($id)` | `$lettermint->webhooks->regenerateSecret($id)` |
| `$api->webhooks->deliveries($id, $query)` | `$lettermint->webhooks->deliveries->list($id, $query)`, `$lettermint->webhooks->deliveries->iterate($id, $query)` |
| `$api->webhooks->delivery($id, $deliveryId)` | `$lettermint->webhooks->deliveries->retrieve($id, $deliveryId)` |

`messages->reschedule()` and `messages->cancel()` accept either token: the team token when configured, otherwise the sending token. This lets a sending-only client cancel the scheduled email it sent.

### Query parameters

Write bracketed names as nested arrays. Lists of values are joined with commas, lists of arrays are indexed, booleans are sent as `1`/`0`, and `null` values are left out.

| 2.x | 3.0 |
| --- | --- |
| `['page[size]' => 30, 'page[cursor]' => $c]` | `['page' => ['size' => 30, 'cursor' => $c]]` |
| `['filter[status]' => 'verified']` | `['filter' => ['status' => 'verified']]` |
| `['sort' => '-created_at,domain']` | `['sort' => ['-created_at', 'domain']]` |
| `['filter[tags][0][name]' => 'a', 'filter[tags][0][value]' => 'b']` | `['filter' => ['tags' => [['name' => 'a', 'value' => 'b']]]]` |
| `['filter[enabled]' => 'true']` | `['filter' => ['enabled' => true]]` |
| webhooks: `['cursor' => $c]` | unchanged: `['cursor' => $c]` (these lists use `cursor`, not `page[cursor]`) |

### Path parameters

IDs are still URL-encoded. An empty ID, `.` or `..` now throws `LettermintConfigException` before the request; 2.x let Guzzle normalise them into another path.

## Errors

2.x threw a generic `\Exception` (code 0) for every HTTP or decode failure, and `\UnexpectedValueException` or `\InvalidArgumentException` in some places. Every 3.0 exception extends `Lettermint\Exceptions\LettermintException` (a `RuntimeException`).

| Situation | 2.x | 3.0 |
| --- | --- | --- |
| HTTP 400 and other 4xx | `\Exception('API request failed: …')`, code 0 | `ApiException` (`status`, `errorCode`, `details`, `body`; `getCode()` is the status) |
| HTTP 401 | `\Exception` | `AuthenticationException` |
| HTTP 403 | `\Exception` | `PermissionException` |
| HTTP 404 | `\Exception` | `NotFoundException` |
| HTTP 409 | `\Exception` | `ConflictException` |
| HTTP 422 | `\Exception` | `ValidationException` (`errors`) |
| HTTP 429 | `\Exception` | `RateLimitException` (`retryAfter` in seconds) |
| HTTP 5xx | `\Exception` | `ServerException` |
| Empty or invalid JSON body, HTML error page | `\Exception('Could not decode API response')` | `UnexpectedResponseException` (`status`, `bodyExcerpt`) |
| Redirect (3xx) | followed by Guzzle, with the token | `RedirectException` (`status`); never followed |
| Timeout | `\Exception` | `TimeoutException` (`timeout`); covers the whole request |
| Network failure | `\Exception` | `ConnectionException` |
| Invalid tags | `\InvalidArgumentException` | `LettermintValidationException` (`field`) |
| Missing or wrong token, bad option or ID | — / `\InvalidArgumentException` | `LettermintConfigException` |

`errorCode` comes from `{"error": {"code": …}}`, or from a string `error` field. The message is the API's message, or the HTTP reason phrase.

```php
// 2.x
try {
    $email->send($payload);
} catch (\Exception $e) {
    if (str_contains($e->getMessage(), '429')) {
        retryLater();
    }
}

// 3.0
use Lettermint\Exceptions\RateLimitException;
use Lettermint\Exceptions\ValidationException;

try {
    $lettermint->emails->send($payload, idempotencyKey: $key);
} catch (ValidationException $e) {
    log($e->status, $e->errorCode, $e->errors);
} catch (RateLimitException $e) {
    retryLater($e->retryAfter);
}
```

The SDK does not retry requests. Pass an idempotency key when you retry a send.

SDK exceptions no longer wrap the Guzzle exception as `getPrevious()`: it holds the request and with it the token. The Guzzle message is part of the `ConnectionException` message.

## Webhooks

`verify()` now takes the raw body first and the headers second, and replaces `verifyHeaders()`. The old `verify($payload, $signature, $timestamp)` is now `verifySignature()`. The static `Webhook::verifySignature($payload, $signature, $secret, …)` is removed. Both methods return a `WebhookPayload` instead of an array.

```php
// 2.x
$webhook = new Lettermint\Webhook($secret, 300);
$payload = $webhook->verifyHeaders($headers, $rawBody);
$payload = $webhook->verify($rawBody, $signatureHeader, (int) $deliveryHeader);
$payload = Lettermint\Webhook::verifySignature($rawBody, $signatureHeader, $secret);
$event = $payload['event'];

// 3.0
$webhook = new Lettermint\Webhook($secret, 300);
$payload = $webhook->verify($rawBody, $request->headers);   // array, HeaderBag or PSR-7 message
$payload = $webhook->verifySignature($rawBody, $signatureHeader, $deliveryHeader);
$event = $payload->event;                                   // $payload['event'] works too
$all = $payload->toArray();
```

- The headers may be an array of strings or of lists (`$request->headers->all()`), a Symfony or Laravel `HeaderBag`, or a PSR-7 message. Header names are case-insensitive; 2.x needed the headers flattened to strings.
- Both `X-Lettermint-Signature` and `X-Lettermint-Delivery` are required, as in 2.8.
- Any matching `v1` signature is accepted (key rotation); 2.x checked only the last one.
- `InvalidSignatureException`, `TimestampToleranceException` and `JsonDecodeException` are removed. Every failure is a `WebhookVerificationException` with a `reason`: `signature_header_missing`, `signature_header_malformed`, `delivery_header_missing`, `delivery_timestamp_mismatch`, `timestamp_out_of_tolerance`, `signature_mismatch`, `body_invalid` or `payload_invalid` (constants on the class).
- An empty secret or a negative tolerance throws `LettermintConfigException` (2.x: `\InvalidArgumentException` for the secret; a negative tolerance was accepted).
- A third constructor argument, `clock`, returns the current Unix time for tests.

## Type names

The response classes moved from `Lettermint\Objects\` and `Lettermint\Responses\` to `Lettermint\Types\`, and use the names of the API specification (lettermint#2582). Request bodies and query parameters are no longer classes: they are PHPStan array shapes on `Lettermint\Types\ApiTypes` (`@phpstan-import-type SendMailRequest from \Lettermint\Types\ApiTypes`). Enums are classes with constants (`Lettermint\Types\MessageStatus::DELIVERED`), not `ApiTypes` aliases; type them as `string`.

`MessageResponse` keeps its name but changes its meaning: in 2.x it was a message (`messages->retrieve()`); in 3.0 it is the `{message}` confirmation that delete and verify calls return. A message is `MessageData`.

Classes and aliases that keep their name only change namespace (`Lettermint\Objects\DomainData` is `Lettermint\Types\DomainData`). The others:

| 2.x | 3.0 |
| --- | --- |
| `Responses\AnalyticsResponse` | `Types\AnalyticsResponse` |
| `Objects\AnalyticsRequest` | `ApiTypes` shape `AnalyticsQuery` |
| `Responses\BlockedFileTypesResponse` | `Types\BlockedFileTypes` |
| `ApiTypes` `CancelScheduledMessageResponse`, `RescheduleMessageResponse`, `Responses\ScheduledMessageResponse` | `Types\ScheduledMessage` |
| `Responses\CreateProjectResponse` | `Types\ProjectCreatedData` |
| `Responses\CreateRouteResponse`, `Responses\UpdateRouteResponse` | `Types\RouteMutationResponse` |
| `Responses\CreateSuppressionResponse` | `Types\SuppressionStoreResponse` |
| `Responses\CreateWebhookResponse`, `Responses\RegenerateWebhookSecretResponse` | `Types\WebhookSecretResponse` |
| `ApiTypes` `CursorPage`, `CursorPaginator` | `Types\CursorPage` (generic class) |
| `Responses\DeleteDomainResponse`, `DeleteProjectResponse`, `DeleteRouteResponse`, `DeleteWebhookResponse`, `VerifyDnsRecordResponse` | `Types\MessageResponse` |
| `Responses\DeleteSuppressionResponse` | `Types\DeleteSuppressionResponse` |
| `Responses\DomainListResponse` | `Types\ListDomainsResponse` |
| `Responses\DomainResponse` | `Types\DomainData` |
| `Responses\MessageEventsResponse` | `Types\ListMessageEventsResponse` |
| `Responses\MessageListResponse` | `Types\ListMessagesResponse` |
| `Responses\MessageResponse` | `Types\MessageData` |
| `Objects\MessageTag` (input value object) | an array `['name' => …, 'value' => …]` (`ApiTypes` shape `MessageTagInput`); `Types\MessageTag` is the tag in responses |
| `Responses\ProjectListResponse` | `Types\ListProjectsResponse` |
| `Responses\ProjectResponse` | `Types\ProjectData` |
| `Responses\ProjectRoutesResponse` | `Types\ListRoutesResponse` |
| `Responses\ReportForwardingResponse` | `Types\GetReportForwardingResponse`, `UpdateReportForwardingResponse`, `VerifyReportForwardingResponse`, `ResendReportForwardingCodeResponse` (one per method) |
| `Responses\RouteResponse` | `Types\RouteData` |
| `Responses\SendBatchMailResponse` (`->data`) | a `list<Types\SendMailResponse>` (`ApiTypes` alias `SendBatchMailResponse`) |
| `ApiTypes` `StatsQuery` | `ApiTypes` shape `GetStatsQuery` |
| `Responses\StatsResponse` | `Types\StatsData` |
| `Responses\SuppressionListResponse` | `Types\ListSuppressionsResponse` |
| `Responses\TeamMembersAssignmentUpdateResponse`, `Responses\TeamMembersShowResponse` | `Types\TeamMemberData` |
| `Responses\TeamMembersResponse` | `Types\ListTeamMembersResponse` |
| `Responses\TeamResponse` | `Types\TeamData` |
| `Responses\TeamRolesResponse` | `Types\TeamRoleListResponse` |
| `Responses\TeamUsageResponse` | `Types\TeamUsageDetailData` |
| `Responses\UpdateDomainProjectsResponse` | `Types\DomainMutationResponse` |
| `Responses\UpdateProjectResponse` | `Types\ProjectMutationResponse` |
| `Responses\UpdateTeamResponse` | `Types\TeamMutationResponse` |
| `Responses\UpdateWebhookResponse` | `Types\WebhookMutationResponse` |
| `Responses\VerifyDnsRecordsResponse` | `Types\DnsVerificationSuccessResponse` |
| `Responses\VerifyInboundDomainResponse` | `Types\InboundDomainVerificationResponse` |
| `Responses\WebhookDeliveriesResponse` | `Types\ListWebhookDeliveriesResponse` |
| `Responses\WebhookDeliveryResponse` | `Types\WebhookDeliveryData` |
| `Responses\WebhookListResponse` | `Types\ListWebhooksResponse` |
| `Responses\WebhookResponse` | `Types\WebhookData` |
| `Objects\SendMailRequest`, `RescheduleMessageRequest`, `ReportForwardingRequest`, `VerifyReportForwardingRequest`, `StoreDomainData`, `StoreProjectData`, `StoreRouteData`, `StoreSuppressionData`, `StoreWebhookData`, `UpdateDomainProjectsData`, `UpdateProjectData`, `UpdateRouteData`, `UpdateRouteInboundSettingsData`, `UpdateRouteSettingsData`, `UpdateTeamData`, `UpdateTeamMemberAssignmentData`, `UpdateWebhookData`, `WebhookBasicAuthData` | `ApiTypes` shapes with the same names (request bodies are arrays) |
| `ApiTypes` response shapes (`DomainData`, `MessageData`, … ) | the `Types\` classes with the same names |
| `ApiTypes` enum aliases (`MessageStatus`, `WebhookEvent`, `SandboxResult`, … ) | `Types\` enum classes with the same names; values are `string` |
| `ApiTypes` `ApiObject` | `Lettermint\ApiObject` (base class of every response class) |

### Removed types

lettermint#2582 removed these schemas from the API specification:

| 2.x | 3.0 |
| --- | --- |
| `Objects\AnalyticsResponseData` | Removed. Use `Types\AnalyticsResponse` (`data` is `Types\AnalyticsResults`). |
| `Objects\StatsRequestData` | Removed. Use the `ApiTypes` shape `GetStatsQuery`, the parameters of `stats->retrieve()`. |
| Message list `meta` (`MessageIndexResponseMeta`) | Removed; not exported by 2.x. Lists are flat `CursorPage`s. |
| Message events `meta` (`MessageEventsResponseMeta`) | Removed; not exported by 2.x. |
| `SuppressionStoreResponseMessage1` | Removed; not exported by 2.x. `SuppressionStoreResponse::$message` is a `string`. |

### Removed classes

| 2.x | 3.0 |
| --- | --- |
| `Lettermint::email($token, $baseUrl, $timeout)` | `(new Lettermint(sendingToken: $token, …))->emails` |
| `Lettermint::api($token, $baseUrl, $timeout)` | `new Lettermint(teamToken: $token, …)` |
| `$lettermint->email` | `$lettermint->emails->send()` / `$lettermint->emails->compose()` |
| `Lettermint\Client\ApiClient` | `Lettermint\Lettermint` |
| `Lettermint\Client\HttpClient`, `Lettermint\Client\Auth\AuthStrategy`, `SendingApiTokenAuth`, `TeamBearerTokenAuth` | Removed. Pass a Guzzle client as `httpClient:` to customise HTTP. |
| `Lettermint\Endpoints\EmailEndpoint` | `Lettermint\Resources\Emails` (from `$lettermint->emails`) and `Lettermint\EmailBuilder` (from `compose()`) |
| `Lettermint\Endpoints\Endpoint`, `DomainsEndpoint`, `MessagesEndpoint`, `ProjectsEndpoint`, `RoutesEndpoint`, `StatsEndpoint`, `SuppressionsEndpoint`, `TeamEndpoint`, `WebhooksEndpoint` | `Lettermint\Resources\Domains`, `Messages`, `Projects` (with `ReportForwarding`), `Routes`, `Stats`, `Suppressions`, `Team` (with `TeamMembers`), `Webhooks` (with `WebhookDeliveries`); use the properties of `Lettermint` |
| `Lettermint\Resource` | `Lettermint\ApiObject` |
| `Lettermint\Objects\*`, `Lettermint\Responses\*` | `Lettermint\Types\*` (see the table above) |
| `Lettermint\Exceptions\InvalidSignatureException`, `TimestampToleranceException`, `JsonDecodeException` | `WebhookVerificationException` with a `reason` |
| `Webhook::verifyHeaders($headers, $body)` | `$webhook->verify($body, $headers)` |
| `Webhook::verify($body, $signature, $timestamp)` | `$webhook->verifySignature($body, $signature, $timestamp)` |
| static `Webhook::verifySignature($body, $signature, $secret, $timestamp, $tolerance)` | `(new Webhook($secret, $tolerance))->verifySignature($body, $signature, $timestamp)` |

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
