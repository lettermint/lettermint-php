<?php

// Type-level checks of the public API, analysed (not run) by PHPStan:
// `vendor/bin/phpstan analyse` includes this file.

declare(strict_types=1);

use Lettermint\Lettermint;
use Lettermint\Types\AnalyticsResponse;
use Lettermint\Types\DomainListData;
use Lettermint\Types\ListDomainsResponse;
use Lettermint\Types\SendMailResponse;
use Lettermint\Webhook;

use function PHPStan\Testing\assertType;

function lettermintTypeAssertions(Lettermint $lettermint): void
{
    $sent = $lettermint->emails->send(['from' => 'a@example.test', 'to' => ['b@example.test'], 'subject' => 'Hi']);
    assertType(SendMailResponse::class, $sent);
    assertType('string', $sent->message_id);
    assertType('string', $sent->status);
    assertType('string|null', $sent->scheduled_at);
    assertType('list<Lettermint\Types\SendMailResponse>', $lettermint->emails->sendBatch([]));
    assertType(SendMailResponse::class, $lettermint->emails->compose()->from('a@example.test')->send(idempotencyKey: 'k'));

    $page = $lettermint->domains->list(['page' => ['size' => 30], 'filter' => ['status' => 'verified'], 'sort' => ['-created_at']]);
    assertType(ListDomainsResponse::class, $page);
    assertType('list<Lettermint\Types\DomainListData>', $page->data);
    assertType('string|null', $page->next_cursor);
    foreach ($lettermint->domains->iterate() as $domain) {
        assertType(DomainListData::class, $domain);
    }
    foreach ($page as $item) {
        assertType(DomainListData::class, $item);
    }

    foreach ($lettermint->analyticsPages(['metrics' => ['delivered'], 'include' => ['breakdown'], 'group_by' => ['recipient_domain']]) as $analytics) {
        assertType(AnalyticsResponse::class, $analytics);
        assertType('list<Lettermint\Types\AnalyticsBreakdownRow>|null', $analytics->data->breakdown);
        assertType('string|null', $analytics->pagination->next_cursor);
    }

    assertType('list<Lettermint\Types\DomainDnsRecordData>|null', $lettermint->domains->retrieve('d')->dns_records);
    assertType('string', $lettermint->messages->html('m'));
    assertType('string|null', $lettermint->routes->retrieve('r')->inbound_route_domain);
    assertType('string', (new Webhook('whsec_x'))->verify('{}', [])->event);
}
