<?php

// Type-level checks of the public API, analysed (not run) by PHPStan:
// `vendor/bin/phpstan analyse` includes this file.

declare(strict_types=1);

use Lettermint\Lettermint;
use Lettermint\Types\AnalyticsResponse;
use Lettermint\Types\DomainListData;
use Lettermint\Types\ListDomainsResponse;
use Lettermint\Types\PendingSendMailResponse;
use Lettermint\Types\ProjectApiToken;
use Lettermint\Types\ProjectApiTokenCreatedResponse;
use Lettermint\Types\RetryWebhookDeliveryResponse;
use Lettermint\Types\SendMailResponse;
use Lettermint\Types\TeamApiToken;
use Lettermint\Types\TeamApiTokenCreatedResponse;
use Lettermint\Types\TeamInviteData;
use Lettermint\Types\TeamRoleData;
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
    assertType('list<Lettermint\Types\SendMailResponse>', $lettermint->emails->sendBatch([
        ['from' => 'a@example.test', 'to' => ['b@example.test'], 'subject' => 'Hi', 'idempotency_key' => 'k1'],
        $lettermint->emails->compose()->from('a@example.test')->idempotencyKey('k2'),
    ], idempotencyKey: 'batch'));
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
    assertType(PendingSendMailResponse::class, $lettermint->messages->resend('m', ['recipients' => ['a@example.test']], 'k'));
    assertType('string|null', $lettermint->messages->retrieve('m')->resent_from_message_id);
    assertType('string|null', $lettermint->routes->retrieve('r')->inbound_route_domain);
    assertType(RetryWebhookDeliveryResponse::class, $lettermint->webhooks->deliveries->retry('w', 'd'));
    assertType('string', $lettermint->webhooks->deliveries->retry('w', 'd')->delivery_id);
    assertType('string|null', $lettermint->webhooks->deliveries->retrieve('w', 'd')->next_retry_at);
    $invite = $lettermint->team->invites->create(['email' => 'a@example.test', 'role_id' => 'r', 'project_access' => ['scope' => 'selected', 'project_ids' => ['p']]]);
    assertType(TeamInviteData::class, $invite);
    assertType('string', $invite->role->name);
    assertType('list<Lettermint\Types\TeamInviteData>', $lettermint->team->invites->list(['page' => ['size' => 10]])->data);
    foreach ($lettermint->team->invites->iterate() as $pending) {
        assertType(TeamInviteData::class, $pending);
    }
    assertType('string', $lettermint->team->invites->revoke('i')->message);
    $role = $lettermint->team->createRole(['name' => 'Support', 'description' => null, 'permissions' => ['messages:read']]);
    assertType(TeamRoleData::class, $role);
    assertType('string|null', $role->description);
    assertType(TeamRoleData::class, $lettermint->team->duplicateRole('r', ['name' => 'Copy']));
    assertType('string', $lettermint->team->deleteRole('r', ['replacement_role_id' => 'r2'])->message);
    assertType('string', $lettermint->team->members->remove('u')->message);
    assertType('list<Lettermint\Types\TeamMemberData>', $lettermint->team->members->list(['filter' => ['project_id' => 'p']])->data);
    $projectToken = $lettermint->projects->apiTokens->create('p', ['name' => 'Production', 'short_token' => true]);
    assertType(ProjectApiTokenCreatedResponse::class, $projectToken);
    assertType('string', $projectToken->data->token);
    assertType('Lettermint\Types\SmtpCredentials|null', $projectToken->smtp);
    assertType('list<Lettermint\Types\ProjectApiToken>', $lettermint->projects->apiTokens->list('p', ['page' => ['size' => 10], 'sort' => ['-created_at']])->data);
    foreach ($lettermint->projects->apiTokens->iterate('p') as $token) {
        assertType(ProjectApiToken::class, $token);
    }
    assertType('string', $lettermint->projects->apiTokens->delete('p', 't')->message);
    $teamToken = $lettermint->team->apiTokens->create(['name' => 'Terraform', 'abilities' => ['read:projects'], 'expires_at' => null]);
    assertType(TeamApiTokenCreatedResponse::class, $teamToken);
    assertType('string', $teamToken->data->token);
    assertType('list<string>', $teamToken->data->abilities);
    assertType('string|null', $teamToken->data->expires_at);
    assertType('list<Lettermint\Types\TeamApiToken>', $lettermint->team->apiTokens->list(['page' => ['size' => 10]])->data);
    foreach ($lettermint->team->apiTokens->iterate() as $token) {
        assertType(TeamApiToken::class, $token);
    }
    assertType('string', $lettermint->team->apiTokens->delete('t')->message);
    assertType('string', (new Webhook('whsec_x'))->verify('{}', [])->event);
}
