<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Lettermint\ApiObject;
use Lettermint\Exceptions\LettermintConfigException;
use Lettermint\Exceptions\UnexpectedResponseException;
use Lettermint\Lettermint;
use Lettermint\Tests\Support\RecordedRequest;
use Lettermint\Types\DomainListData;
use Lettermint\Types\MessageEventData;
use Lettermint\Types\MessageListData;
use Lettermint\Types\Operations;
use Lettermint\Types\ProjectListData;
use Lettermint\Types\RouteData;
use Lettermint\Types\RouteListData;
use Lettermint\Types\SendMailResponse;
use Lettermint\Types\SuppressedRecipientData;
use Lettermint\Types\TeamMemberData;
use Lettermint\Types\WebhookDeliveryListData;
use Lettermint\Types\WebhookListData;

const IDS = [
    'domainId' => 'domain/1',
    'recordId' => 'record 1',
    'messageId' => 'message?1',
    'projectId' => 'project#1',
    'routeId' => 'route_1',
    'suppressionId' => 'suppression_1',
    'userId' => 'user/id',
    'webhookId' => 'webhook_1',
    'deliveryId' => 'delivery_1',
];

/**
 * Every operation of the API, called through its public SDK method.
 *
 * @return array<string, Closure(Lettermint): mixed>
 */
function operationCalls(): array
{
    $ids = IDS;

    return [
        'DELETE /domains/{domainId}' => fn (Lettermint $l) => $l->domains->delete($ids['domainId']),
        'DELETE /projects/{projectId}' => fn (Lettermint $l) => $l->projects->delete($ids['projectId']),
        'DELETE /projects/{projectId}/report-forwarding' => fn (Lettermint $l) => $l->projects->reportForwarding->delete($ids['projectId']),
        'DELETE /routes/{routeId}' => fn (Lettermint $l) => $l->routes->delete($ids['routeId']),
        'DELETE /suppressions/{suppressionId}' => fn (Lettermint $l) => $l->suppressions->delete($ids['suppressionId']),
        'DELETE /webhooks/{webhookId}' => fn (Lettermint $l) => $l->webhooks->delete($ids['webhookId']),
        'GET /blocked-file-types' => fn (Lettermint $l) => $l->blockedFileTypes(),
        'GET /domains' => fn (Lettermint $l) => $l->domains->list(),
        'GET /domains/{domainId}' => fn (Lettermint $l) => $l->domains->retrieve($ids['domainId']),
        'GET /messages' => fn (Lettermint $l) => $l->messages->list(),
        'GET /messages/{messageId}' => fn (Lettermint $l) => $l->messages->retrieve($ids['messageId']),
        'GET /messages/{messageId}/events' => fn (Lettermint $l) => $l->messages->events($ids['messageId']),
        'GET /messages/{messageId}/html' => fn (Lettermint $l) => $l->messages->html($ids['messageId']),
        'GET /messages/{messageId}/source' => fn (Lettermint $l) => $l->messages->source($ids['messageId']),
        'GET /messages/{messageId}/text' => fn (Lettermint $l) => $l->messages->text($ids['messageId']),
        'GET /ping' => fn (Lettermint $l) => $l->ping(),
        'GET /projects' => fn (Lettermint $l) => $l->projects->list(),
        'GET /projects/{projectId}' => fn (Lettermint $l) => $l->projects->retrieve($ids['projectId']),
        'GET /projects/{projectId}/report-forwarding' => fn (Lettermint $l) => $l->projects->reportForwarding->retrieve($ids['projectId']),
        'GET /projects/{projectId}/routes' => fn (Lettermint $l) => $l->routes->list($ids['projectId']),
        'GET /routes/{routeId}' => fn (Lettermint $l) => $l->routes->retrieve($ids['routeId']),
        'GET /stats' => fn (Lettermint $l) => $l->stats->retrieve(['from' => '2026-01-01', 'to' => '2026-01-31']),
        'GET /suppressions' => fn (Lettermint $l) => $l->suppressions->list(),
        'GET /team' => fn (Lettermint $l) => $l->team->retrieve(),
        'GET /team/members' => fn (Lettermint $l) => $l->team->members->list(),
        'GET /team/members/{userId}' => fn (Lettermint $l) => $l->team->members->retrieve($ids['userId']),
        'GET /team/roles' => fn (Lettermint $l) => $l->team->roles(),
        'GET /team/usage' => fn (Lettermint $l) => $l->team->usage(),
        'GET /webhooks' => fn (Lettermint $l) => $l->webhooks->list(),
        'GET /webhooks/{webhookId}' => fn (Lettermint $l) => $l->webhooks->retrieve($ids['webhookId']),
        'GET /webhooks/{webhookId}/deliveries' => fn (Lettermint $l) => $l->webhooks->deliveries->list($ids['webhookId']),
        'GET /webhooks/{webhookId}/deliveries/{deliveryId}' => fn (Lettermint $l) => $l->webhooks->deliveries->retrieve($ids['webhookId'], $ids['deliveryId']),
        'PATCH /messages/{messageId}' => fn (Lettermint $l) => $l->messages->reschedule($ids['messageId'], ['scheduled_at' => '2026-10-01T09:00:00Z']),
        'POST /analytics' => fn (Lettermint $l) => $l->analytics(['metrics' => ['accepted']]),
        'POST /domains' => fn (Lettermint $l) => $l->domains->create(['domain' => 'example.test']),
        'POST /domains/{domainId}/dns-records/verify' => fn (Lettermint $l) => $l->domains->verifyDnsRecords($ids['domainId']),
        'POST /domains/{domainId}/dns-records/{recordId}/verify' => fn (Lettermint $l) => $l->domains->verifyDnsRecord($ids['domainId'], $ids['recordId']),
        'POST /messages/{messageId}/cancel' => fn (Lettermint $l) => $l->messages->cancel($ids['messageId']),
        'POST /messages/{messageId}/process' => fn (Lettermint $l) => $l->messages->process($ids['messageId']),
        'POST /projects' => fn (Lettermint $l) => $l->projects->create(['name' => 'Production']),
        'POST /projects/{projectId}/report-forwarding/resend-code' => fn (Lettermint $l) => $l->projects->reportForwarding->resendCode($ids['projectId']),
        'POST /projects/{projectId}/report-forwarding/verify' => fn (Lettermint $l) => $l->projects->reportForwarding->verify($ids['projectId'], ['code' => '123456']),
        'POST /projects/{projectId}/rotate-token' => fn (Lettermint $l) => $l->projects->rotateToken($ids['projectId']),
        'POST /projects/{projectId}/routes' => fn (Lettermint $l) => $l->routes->create($ids['projectId'], ['name' => 'Inbound', 'route_type' => 'inbound']),
        'POST /routes/{routeId}/verify-inbound-domain' => fn (Lettermint $l) => $l->routes->verifyInboundDomain($ids['routeId']),
        'POST /send' => fn (Lettermint $l) => $l->emails->send(['from' => 'a@example.test', 'to' => ['b@example.test'], 'subject' => 'x']),
        'POST /send/batch' => fn (Lettermint $l) => $l->emails->sendBatch([['from' => 'a@example.test', 'to' => ['b@example.test'], 'subject' => 'x']]),
        'POST /suppressions' => fn (Lettermint $l) => $l->suppressions->create(['reason' => 'manual', 'scope' => 'team', 'emails' => ['blocked@example.test']]),
        'POST /webhooks' => fn (Lettermint $l) => $l->webhooks->create(['name' => 'Hook', 'url' => 'https://example.test/hook', 'events' => ['message.sent']]),
        'POST /webhooks/{webhookId}/regenerate-secret' => fn (Lettermint $l) => $l->webhooks->regenerateSecret($ids['webhookId']),
        'POST /webhooks/{webhookId}/test' => fn (Lettermint $l) => $l->webhooks->test($ids['webhookId']),
        'PUT /domains/{domainId}/projects' => fn (Lettermint $l) => $l->domains->updateProjects($ids['domainId'], ['project_ids' => ['p']]),
        'PUT /projects/{projectId}' => fn (Lettermint $l) => $l->projects->update($ids['projectId'], ['name' => 'Renamed']),
        'PUT /projects/{projectId}/report-forwarding' => fn (Lettermint $l) => $l->projects->reportForwarding->update($ids['projectId'], ['destination' => 'reports@example.test']),
        'PUT /routes/{routeId}' => fn (Lettermint $l) => $l->routes->update($ids['routeId'], ['name' => 'Renamed']),
        'PUT /team' => fn (Lettermint $l) => $l->team->update(['name' => 'Acme']),
        'PUT /team/members/{userId}/assignment' => fn (Lettermint $l) => $l->team->members->updateAssignment($ids['userId'], ['role_id' => 'role_1', 'project_access' => ['scope' => 'all']]),
        'PUT /webhooks/{webhookId}' => fn (Lettermint $l) => $l->webhooks->update($ids['webhookId'], ['basic_auth' => null]),
    ];
}

function responseFor(string $key): Response
{
    $response = Operations::ALL[$key]['response'];
    if ($response['type'] === 'empty') {
        return new Response(204);
    }
    if ($response['type'] === 'text') {
        return text(200, 'pong');
    }
    if ($response['type'] === 'SendBatchMailResponse') {
        return json($response['status'][0], [['message_id' => 'm', 'status' => 'pending']]);
    }

    return json($response['status'][0], ['data' => [], 'next_cursor' => null]);
}

describe('operation coverage', function () {
    it('has an SDK method for every operation in the generated table', function () {
        $calls = array_keys(operationCalls());
        $operations = array_keys(Operations::ALL);
        sort($calls);
        sort($operations);
        expect($calls)->toBe($operations)
            ->and($operations)->toHaveCount(58);
    });

    it('calls the operation with the right method, path, token and result type', function (string $key) {
        $operation = Operations::ALL[$key];
        [$lettermint, $api] = client(fn () => responseFor($key));
        $result = operationCalls()[$key]($lettermint);
        expect($api->requests)->toHaveCount(1);
        $request = $api->last();
        $path = (string) preg_replace_callback('/\{(\w+)\}/', fn (array $m): string => rawurlencode(IDS[$m[1]]), $operation['path']);
        expect($request->method)->toBe($operation['method'])
            ->and(explode('?', $request->url)[0])->toBe(BASE_URL.$path)
            ->and($request->headers['accept'])->toBe('application/json');
        if ($operation['auth'] === 'sending') {
            expect($request->headers['x-lettermint-token'])->toBe(SENDING_TOKEN)
                ->and($request->headers)->not->toHaveKey('authorization');
        } else {
            expect($request->headers['authorization'])->toBe('Bearer '.TEAM_TOKEN)
                ->and($request->headers)->not->toHaveKey('x-lettermint-token');
        }
        if ($operation['request'] !== null) {
            expect($request->headers['content-type'])->toBe('application/json');
        } else {
            expect($request->rawBody)->toBe('')
                ->and($request->headers)->not->toHaveKey('content-type');
        }
        $type = $operation['response']['type'];
        match (true) {
            $type === 'empty' => expect($result)->toBeNull(),
            $type === 'text' => expect($result)->toBe('pong'),
            $type === 'SendBatchMailResponse' => expect($result[0])->toBeInstanceOf(SendMailResponse::class),
            default => expect($result)->toBeInstanceOf(ApiObject::class)
                ->and(get_class($result))->toBe('Lettermint\\Types\\'.$type),
        };
    })->with(array_keys(Operations::ALL));
});

describe('request bodies and options', function () {
    it('sends request bodies as JSON', function () {
        [$lettermint, $api] = client();
        $assignment = ['role_id' => 'role_123', 'project_access' => ['scope' => 'all']];
        $lettermint->team->members->updateAssignment('user/id', $assignment);
        expect($api->last()->json())->toBe($assignment);
        $lettermint->messages->reschedule('message/id', ['scheduled_at' => '2026-08-27T09:00:00Z']);
        expect($api->last()->json())->toBe(['scheduled_at' => '2026-08-27T09:00:00Z']);
    });

    it('sends an Idempotency-Key for processing an inbound message', function () {
        [$lettermint, $api] = client(fn () => json(202, ['data' => ['message_id' => 'm']]));
        $lettermint->messages->process('m', idempotencyKey: 'process-1');
        $lettermint->messages->process('m');
        expect($api->requests[0]->headers['idempotency-key'])->toBe('process-1')
            ->and($api->requests[1]->headers)->not->toHaveKey('idempotency-key');
    });

    it('keeps the webhook basic-auth state', function (array $state) {
        [$lettermint, $api] = client(fn () => json(200, ['data' => ['has_basic_auth' => true]]));
        $create = ['name' => 'Fixture', 'url' => 'https://example.test/hook', 'events' => ['message.sent'], ...$state];
        expect($lettermint->webhooks->create($create)->data?->has_basic_auth)->toBeTrue()
            ->and($lettermint->webhooks->update('webhook-id', $state)->data?->has_basic_auth)->toBeTrue()
            ->and($api->requests[0]->json())->toBe($create)
            ->and($api->requests[1]->rawBody)->toBe(json_encode($state === [] ? new stdClass : $state));
    })->with([
        'omit' => [[]],
        'set' => [['basic_auth' => ['username' => ' fixture user ', 'password' => '']]],
        'remove' => [['basic_auth' => null]],
    ]);

    it('sends analytics queries and report forwarding payloads', function () {
        $analytics = [
            'metrics' => ['accepted', 'delivered'],
            'include' => ['summary', 'time_series'],
            'interval' => 'day',
            'filters' => [['dimension' => 'project_id', 'operator' => 'eq', 'values' => ['project/id']]],
            'sort' => ['metric' => 'accepted', 'direction' => 'desc'],
            'limit' => 10,
        ];
        [$lettermint, $api] = client(fn () => json(200, ['data' => ['summary' => ['metrics' => ['accepted' => 12, 'delivery_rate' => null]]], 'meta' => ['timezone' => 'UTC']]));
        $result = $lettermint->analytics($analytics);
        expect($result->meta?->timezone)->toBe('UTC')
            ->and($result->data?->summary?->metrics?->accepted)->toBe(12)
            ->and($api->last()->json())->toBe($analytics);
        $lettermint->projects->reportForwarding->update('project/id', ['destination' => 'reports@example.test']);
        expect($api->last()->json())->toBe(['destination' => 'reports@example.test']);
    });

    it('keeps an inbound route domain that is set, null or absent', function (?string $domain, bool $present) {
        $route = [
            'id' => 'route_1', 'project_id' => 'project_1', 'slug' => 'incoming', 'name' => 'Incoming',
            'route_type' => 'inbound', 'is_default' => false, 'created_at' => '2026-10-01T12:00:00Z', 'updated_at' => '2026-10-01T12:00:00Z',
            ...($present ? ['inbound_route_domain' => $domain] : []),
        ];
        [$lettermint] = client(fn () => json(200, $route));
        $result = $lettermint->routes->retrieve('route_1');
        expect($result)->toBeInstanceOf(RouteData::class)
            ->and($result->inbound_route_domain)->toBe($domain)
            ->and($result->has('inbound_route_domain'))->toBe($present)
            ->and($result->toArray())->toBe($route);
    })->with([
        'set' => ['incoming.example.com', true],
        'null' => [null, true],
        'absent' => [null, false],
    ]);
});

describe('query parameters', function () {
    it('serializes nested arrays to the bracket syntax', function () {
        [$lettermint, $api] = client(fn () => json(200, ['data' => [], 'next_cursor' => null]));
        $lettermint->domains->list([
            'page' => ['size' => 10, 'cursor' => 'abc'],
            'filter' => ['status' => 'verified', 'domain' => 'acme'],
            'sort' => ['-created_at', 'domain'],
        ]);
        expect($api->last()->query)->toBe([
            'page[size]' => '10',
            'page[cursor]' => 'abc',
            'filter[status]' => 'verified',
            'filter[domain]' => 'acme',
            'sort' => '-created_at,domain',
        ])->and($api->last()->url)->toContain('page%5Bsize%5D=10');
    });

    it('serializes booleans as 1/0, tag pairs with indexes and skips null', function () {
        [$lettermint, $api] = client(fn () => json(200, ['data' => [], 'next_cursor' => null]));
        $lettermint->messages->list(['filter' => [
            'tags' => [['name' => 'campaign', 'value' => 'welcome'], ['name' => 'tier', 'value' => 'gold']],
            'status' => null,
            'search' => 'hello world',
        ]]);
        expect($api->last()->url)->toContain('filter%5Bsearch%5D=hello+world')
            ->and($api->last()->query)->toBe([
                'filter[tags][0][name]' => 'campaign',
                'filter[tags][0][value]' => 'welcome',
                'filter[tags][1][name]' => 'tier',
                'filter[tags][1][value]' => 'gold',
                'filter[search]' => 'hello world',
            ]);
        $lettermint->webhooks->list(['filter' => ['enabled' => false], 'cursor' => 'c1', 'page' => ['size' => 5]]);
        expect($api->last()->query)->toBe(['filter[enabled]' => '0', 'cursor' => 'c1', 'page[size]' => '5']);
        $lettermint->messages->events('m', ['include_machine_events' => true, 'page' => ['size' => 2]]);
        expect($api->last()->query)->toBe(['include_machine_events' => '1', 'page[size]' => '2']);
        $lettermint->domains->retrieve('d', ['include' => ['dnsRecords', 'projects']]);
        expect($api->last()->query)->toBe(['include' => 'dnsRecords,projects']);
        $lettermint->stats->retrieve(['from' => '2026-01-01', 'to' => '2026-01-31', 'project_id' => null]);
        expect($api->last()->query)->toBe(['from' => '2026-01-01', 'to' => '2026-01-31']);
    });
});

describe('pagination', function () {
    it('follows next_cursor in page[cursor] until it is null', function () {
        $pages = [
            'first' => ['data' => [['id' => 'd1'], ['id' => 'd2']], 'next_cursor' => 'c2'],
            'c2' => ['data' => [['id' => 'd3']], 'next_cursor' => 'c3'],
            'c3' => ['data' => [], 'next_cursor' => null],
        ];
        [$lettermint, $api] = client(fn (RecordedRequest $request) => json(200, $pages[$request->query['page[cursor]'] ?? 'first']));
        $ids = [];
        foreach ($lettermint->domains->iterate(['page' => ['size' => 2], 'filter' => ['status' => 'verified']]) as $domain) {
            $ids[] = $domain->id;
        }
        expect($ids)->toBe(['d1', 'd2', 'd3'])
            ->and(array_map(fn (RecordedRequest $r): array => $r->query, $api->requests))->toBe([
                ['page[size]' => '2', 'filter[status]' => 'verified'],
                ['page[size]' => '2', 'page[cursor]' => 'c2', 'filter[status]' => 'verified'],
                ['page[size]' => '2', 'page[cursor]' => 'c3', 'filter[status]' => 'verified'],
            ]);
    });

    it('uses the cursor parameter where the table says so', function () {
        [$lettermint, $api] = client(fn (RecordedRequest $request) => json(200, isset($request->query['cursor'])
            ? ['data' => [['id' => 'w2']], 'next_cursor' => null]
            : ['data' => [['id' => 'w1']], 'next_cursor' => 'next']));
        $ids = [];
        foreach ($lettermint->webhooks->deliveries->iterate('hook/1') as $delivery) {
            $ids[] = $delivery->id;
        }
        expect($ids)->toBe(['w1', 'w2'])
            ->and($api->requests[1]->url)->toBe(BASE_URL.'/webhooks/hook%2F1/deliveries?cursor=next')
            ->and(Operations::ALL['GET /webhooks/{webhookId}/deliveries']['pagination']['cursorParam'] ?? null)->toBe('cursor');
    });

    it('iterates every list', function (Closure $iterate, string $itemClass) {
        [$lettermint, $api] = client(fn (RecordedRequest $request, int $index) => json(200, $index === 0
            ? ['data' => [['id' => '1']], 'next_cursor' => 'n']
            : ['data' => [['id' => '2']], 'next_cursor' => null]));
        $items = iterator_to_array($iterate($lettermint), false);
        expect(array_map(fn (ApiObject $item): mixed => $item->toArray(), $items))->toBe([['id' => '1'], ['id' => '2']])
            ->and($items[0])->toBeInstanceOf($itemClass)
            ->and($api->requests)->toHaveCount(2);
    })->with([
        'domains' => [fn (Lettermint $l) => $l->domains->iterate(), DomainListData::class],
        'messages' => [fn (Lettermint $l) => $l->messages->iterate(), MessageListData::class],
        'message events' => [fn (Lettermint $l) => $l->messages->iterateEvents('m'), MessageEventData::class],
        'projects' => [fn (Lettermint $l) => $l->projects->iterate(), ProjectListData::class],
        'routes' => [fn (Lettermint $l) => $l->routes->iterate('p'), RouteListData::class],
        'suppressions' => [fn (Lettermint $l) => $l->suppressions->iterate(), SuppressedRecipientData::class],
        'team members' => [fn (Lettermint $l) => $l->team->members->iterate(), TeamMemberData::class],
        'webhooks' => [fn (Lettermint $l) => $l->webhooks->iterate(), WebhookListData::class],
        'webhook deliveries' => [fn (Lettermint $l) => $l->webhooks->deliveries->iterate('w'), WebhookDeliveryListData::class],
    ]);

    it('stops when the API repeats a cursor', function () {
        [$lettermint, $api] = client(fn () => json(200, ['data' => [['id' => 'x']], 'next_cursor' => 'same']));
        $items = iterator_to_array($lettermint->projects->iterate(), false);
        expect($api->requests)->toHaveCount(2)
            ->and($items)->toHaveCount(2);
    });

    it('requests the next page only when the caller gets to it', function () {
        [$lettermint, $api] = client(fn () => json(200, ['data' => [['id' => 'x'], ['id' => 'y']], 'next_cursor' => 'n']));
        foreach ($lettermint->suppressions->iterate() as $item) {
            break;
        }
        expect($api->requests)->toHaveCount(1);
    });

    it('rejects a page without a data list', function () {
        [$lettermint] = client(fn () => json(200, ['data' => 'nope', 'next_cursor' => null]));
        expect(fn () => iterator_to_array($lettermint->domains->iterate()))
            ->toThrow(UnexpectedResponseException::class, 'without a data list');
    });

    it('returns a typed page that can be counted and iterated', function () {
        [$lettermint] = client(fn () => json(200, ['data' => [['id' => 'd1', 'domain' => 'acme.com']], 'next_cursor' => 'n', 'per_page' => 30]));
        $page = $lettermint->domains->list(['page' => ['size' => 30]]);
        expect($page)->toHaveCount(1)
            ->and($page->hasMore())->toBeTrue()
            ->and($page->data[0])->toBeInstanceOf(DomainListData::class)
            ->and(iterator_to_array($page)[0]->domain)->toBe('acme.com');
    });
});

describe('path parameters', function () {
    it('encodes path parameters', function () {
        [$lettermint, $api] = client();
        $lettermint->domains->verifyDnsRecord('domain/../x', 'record?a=b#c');
        expect($api->last()->url)->toBe(BASE_URL.'/domains/domain%2F..%2Fx/dns-records/record%3Fa%3Db%23c/verify');
    });

    it('rejects empty, . and .. before any request', function (string $id) {
        [$lettermint, $api] = client();
        expect(fn () => $lettermint->domains->retrieve($id))
            ->toThrow(LettermintConfigException::class, 'domains.retrieve: domainId must be a non-empty string other than "." and "..".')
            ->and(fn () => $lettermint->webhooks->deliveries->retrieve('w', $id))
            ->toThrow(LettermintConfigException::class, 'webhooks.deliveries.retrieve: deliveryId')
            ->and(fn () => $lettermint->routes->iterate($id))
            ->toThrow(LettermintConfigException::class, 'routes.iterate: projectId')
            ->and($api->requests)->toBeEmpty();
    })->with(['', '.', '..']);
});
