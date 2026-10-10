<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Lettermint\Exceptions\ServerException;
use Lettermint\Exceptions\ValidationException;
use Lettermint\Tests\Support\RecordedRequest;
use Lettermint\Types\AnalyticsBreakdownRow;
use Lettermint\Types\AnalyticsRateBases;
use Lettermint\Types\AnalyticsResponse;

const ANALYTICS_QUERY = [
    'metrics' => ['delivered', 'bounced', 'delivery_rate', 'delivery_latency_p50_ms'],
    'include' => ['summary', 'time_series', 'breakdown'],
    'group_by' => ['recipient_domain'],
    'interval' => 'hour',
    'timezone' => 'Asia/Kolkata',
    'compare' => 'previous_period',
    'limit' => 2,
];

/**
 * An analytics response exactly as the API sends it: the fixture's bytes,
 * not re-encoded, so that `{}` stays an object on the wire.
 */
function analyticsFixture(string $name): Response
{
    return text(200, (string) file_get_contents(__DIR__."/fixtures/analytics-{$name}.json"), ['Content-Type' => 'application/json']);
}

function instant(string $timestamp): string
{
    return (new DateTimeImmutable($timestamp))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
}

describe('analytics responses', function () {
    it('keeps null values, empty rate bases and both timestamp formats', function () {
        [$lettermint, $api] = client(fn () => analyticsFixture('page-1'));
        $result = $lettermint->analytics(ANALYTICS_QUERY);

        expect($result)->toBeInstanceOf(AnalyticsResponse::class)
            ->and($api->last()->json())->toBe(ANALYTICS_QUERY);
        $summary = $result->data->summary;
        expect($summary?->metrics->toArray())->toBe([
            'delivered' => 1200,
            'bounced' => 0,
            'delivery_rate' => 0.9836,
            'delivery_latency_p50_ms' => null,
        ])
            ->and($summary?->metrics->bounced)->toBe(0)
            ->and($summary?->metrics->delivery_latency_p50_ms)->toBeNull()
            ->and($summary?->metrics->has('delivery_latency_p50_ms'))->toBeTrue()
            ->and($summary?->rate_bases->delivery_rate?->toArray())->toBe(['numerator' => 1200, 'denominator' => 1220])
            ->and($summary?->previous?->rate_bases->delivery_rate?->toArray())->toBe(['numerator' => null, 'denominator' => null])
            ->and($summary?->previous?->metrics->delivery_latency_p50_ms)->toBe(812.5)
            ->and($summary?->change['delivery_rate']->toArray())->toBe(['absolute' => null, 'relative' => null, 'percentage_points' => null])
            ->and($summary?->change['delivered']->relative)->toBe(0.0909)
            ->and($summary?->change['delivered']->has('percentage_points'))->toBeFalse();

        [$complete, $unavailable] = $result->data->time_series ?? [];
        expect($complete->from)->toBe('2026-09-15T08:30:00+05:30')
            ->and(instant($complete->from))->toBe('2026-09-15T03:00:00.000000Z')
            ->and($unavailable->available)->toBeFalse()
            ->and($unavailable->partial)->toBeTrue()
            ->and($unavailable->rate_bases)->toBeInstanceOf(AnalyticsRateBases::class)
            ->and($unavailable->rate_bases->toArray())->toBe([])
            ->and(json_encode($unavailable->rate_bases))->toBe('{}')
            ->and($unavailable->rate_bases->delivery_rate)->toBeNull()
            ->and($unavailable->metrics->delivered)->toBeNull()
            ->and($unavailable->metrics->has('delivered'))->toBeTrue();

        expect($result->meta->generated_at)->toBe('2026-09-15T04:12:30.482915Z')
            ->and(instant($result->meta->generated_at))->toBe('2026-09-15T04:12:30.482915Z')
            ->and(instant($result->meta->from))->toBe('2026-09-15T03:00:00.000000Z')
            ->and($result->meta->last_ingested_at)->toBeNull()
            ->and($result->meta->has('last_ingested_at'))->toBeTrue()
            ->and($result->meta->comparison?->toArray())->toBe([
                'from' => '2026-09-15T01:00:00.000000Z',
                'to' => '2026-09-15T03:00:00.000000Z',
                'partial' => false,
            ])
            ->and($result->pagination->toArray())->toBe([
                'total_groups' => 3,
                'returned_groups' => 2,
                'next_cursor' => 'cursor-page-2',
                'truncated' => false,
            ]);
    });

    it('leaves out the sections and comparison a query did not ask for', function () {
        [$lettermint] = client(fn () => analyticsFixture('summary'));
        $result = $lettermint->analytics(['metrics' => ['delivered']]);
        expect($result->data->summary?->rate_bases->toArray())->toBe([])
            ->and($result->data->has('time_series'))->toBeFalse()
            ->and($result->data->time_series)->toBeNull()
            ->and($result->data->has('breakdown'))->toBeFalse()
            ->and($result->data->breakdown)->toBeNull()
            ->and($result->data->summary?->has('previous'))->toBeFalse()
            ->and($result->data->summary?->previous)->toBeNull()
            ->and($result->meta->has('comparison'))->toBeFalse()
            ->and($result->meta->comparison)->toBeNull()
            ->and($result->pagination->has('next_cursor'))->toBeTrue()
            ->and($result->pagination->next_cursor)->toBeNull();
    });

    it('keeps a null dimension value in a breakdown row', function () {
        [$lettermint] = client(fn () => analyticsFixture('page-2'));
        $result = $lettermint->analytics([...ANALYTICS_QUERY, 'cursor' => 'cursor-page-2']);
        expect($result->data->breakdown)->toHaveCount(1);
        [$row] = $result->data->breakdown ?? [];
        expect($row)->toBeInstanceOf(AnalyticsBreakdownRow::class)
            ->and($row->dimensions)->toBe(['recipient_domain' => null])
            ->and($row->metrics->bounced)->toBeNull()
            ->and($row->metrics->has('bounced'))->toBeTrue()
            ->and($row->metrics->delivered)->toBe(50);
    });
});

describe('analyticsPages', function () {
    it('follows next_cursor and yields every response', function () {
        $sent = ANALYTICS_QUERY;
        [$lettermint, $api] = client(fn (RecordedRequest $request, int $index) => analyticsFixture($index === 0 ? 'page-1' : 'page-2'));
        $pages = iterator_to_array($lettermint->analyticsPages($sent), false);

        expect($pages)->toHaveCount(2)
            ->and($pages)->each->toBeInstanceOf(AnalyticsResponse::class)
            ->and($api->requests)->toHaveCount(2)
            ->and(array_map(fn (RecordedRequest $r): string => $r->method.' '.$r->path, $api->requests))->toBe(['POST /analytics', 'POST /analytics'])
            ->and($api->requests[0]->json())->toBe(ANALYTICS_QUERY)
            ->and($api->requests[1]->json())->toBe([...ANALYTICS_QUERY, 'cursor' => 'cursor-page-2'])
            ->and($sent)->toBe(ANALYTICS_QUERY);

        $rows = array_merge(...array_map(fn (AnalyticsResponse $page): array => $page->data->breakdown ?? [], $pages));
        expect(array_map(fn (AnalyticsBreakdownRow $row): ?string => $row->dimensions['recipient_domain'], $rows))->toBe(['gmail.com', 'outlook.com', null])
            ->and($pages[1]->pagination->returned_groups)->toBe(1)
            ->and($pages[1]->pagination->next_cursor)->toBeNull();
    });

    it('makes one request for a query without more pages', function () {
        [$lettermint, $api] = client(fn () => analyticsFixture('summary'));
        $pages = iterator_to_array($lettermint->analyticsPages(['metrics' => ['delivered']]), false);
        expect($pages)->toHaveCount(1)
            ->and($api->requests)->toHaveCount(1);
    });

    it('requests the next page only when it is asked for', function () {
        [$lettermint, $api] = client(fn () => analyticsFixture('page-1'));
        $pages = $lettermint->analyticsPages(ANALYTICS_QUERY);
        expect($api->requests)->toBeEmpty();
        foreach ($pages as $page) {
            expect($page->pagination->next_cursor)->toBe('cursor-page-2');
            break;
        }
        expect($api->requests)->toHaveCount(1);
    });

    it('stops when the API repeats a cursor', function () {
        [$lettermint, $api] = client(fn () => analyticsFixture('page-1'));
        $pages = iterator_to_array($lettermint->analyticsPages(ANALYTICS_QUERY), false);
        expect($pages)->toHaveCount(2)
            ->and($api->requests)->toHaveCount(2);
    });

    it('stops when the API returns the cursor the query started from', function () {
        [$lettermint, $api] = client(fn () => analyticsFixture('page-1'));
        $pages = iterator_to_array($lettermint->analyticsPages([...ANALYTICS_QUERY, 'cursor' => 'cursor-page-2']), false);
        expect($pages)->toHaveCount(1)
            ->and($api->requests)->toHaveCount(1)
            ->and($api->requests[0]->json())->toBe([...ANALYTICS_QUERY, 'cursor' => 'cursor-page-2']);
    });

    it('sends every page with the client timeout and the team token, and surfaces an expired cursor', function () {
        $body = [
            'message' => 'The analytics cursor is invalid or expired. Submit a new query.',
            'errors' => ['cursor' => ['The analytics cursor is invalid or expired. Submit a new query.']],
        ];
        [$lettermint, $api] = client(
            fn (RecordedRequest $request, int $index) => $index === 0 ? analyticsFixture('page-1') : json(422, $body),
            ['timeout' => 7],
        );
        $pages = $lettermint->analyticsPages(ANALYTICS_QUERY);
        expect($pages->valid())->toBeTrue()
            ->and($pages->current())->toBeInstanceOf(AnalyticsResponse::class);
        $error = thrown(fn () => $pages->next());
        expect($error)->toBeInstanceOf(ValidationException::class)
            ->and($error->errors)->toBe($body['errors'])
            ->and($pages->valid())->toBeFalse()
            ->and($api->requests)->toHaveCount(2);
        foreach ($api->requests as $request) {
            expect($request->options)->toMatchArray(['timeout' => 7.0, 'connect_timeout' => 7.0, 'allow_redirects' => false])
                ->and($request->headers['authorization'])->toBe('Bearer '.TEAM_TOKEN)
                ->and($request->headers)->not->toHaveKey('x-lettermint-token');
        }
    });
});

describe('analytics errors', function () {
    it('reads field errors from a 422', function () {
        $body = [
            'message' => 'smtp_response_group can only be used in group_by.',
            'errors' => ['filters' => ['smtp_response_group can only be used in group_by.']],
        ];
        [$lettermint] = client(fn () => json(422, $body));
        $error = thrown(fn () => $lettermint->analytics(ANALYTICS_QUERY));
        expect($error)->toBeInstanceOf(ValidationException::class)
            ->and($error->status)->toBe(422)
            ->and($error->getMessage())->toBe($body['message'])
            ->and($error->errors)->toBe($body['errors']);
    });

    it('reads Retry-After from a 503', function () {
        $body = ['error' => ['code' => 'SERVICE_UNAVAILABLE', 'message' => 'Try again shortly.']];
        [$lettermint] = client(fn () => json(503, $body, ['Retry-After' => '2']));
        $error = thrown(fn () => $lettermint->analytics(ANALYTICS_QUERY));
        expect($error)->toBeInstanceOf(ServerException::class)
            ->and($error->status)->toBe(503)
            ->and($error->errorCode)->toBe('SERVICE_UNAVAILABLE')
            ->and($error->retryAfter)->toBe(2);
    });

    it('has no retryAfter for a 503 or 504 without the header', function () {
        [$unavailable] = client(fn () => json(503, ['message' => 'Analytics is unavailable.']));
        $first = thrown(fn () => $unavailable->analytics(ANALYTICS_QUERY));
        expect($first)->toBeInstanceOf(ServerException::class)
            ->and($first->retryAfter)->toBeNull();

        $message = 'Analytics exceeded the query time limit. Retry with a shorter period or fewer dimensions.';
        [$timedOut] = client(fn () => json(504, ['message' => $message]));
        $second = thrown(fn () => $timedOut->analytics(ANALYTICS_QUERY));
        expect($second)->toBeInstanceOf(ServerException::class)
            ->and($second->status)->toBe(504)
            ->and($second->getMessage())->toBe($message)
            ->and($second->retryAfter)->toBeNull();
    });
});
