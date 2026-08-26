<?php

use Lettermint\Client\ApiClient;
use Lettermint\Endpoints\DomainsEndpoint;
use Lettermint\Endpoints\EmailEndpoint;
use Lettermint\Endpoints\MessagesEndpoint;
use Lettermint\Endpoints\ProjectsEndpoint;
use Lettermint\Endpoints\RoutesEndpoint;
use Lettermint\Endpoints\StatsEndpoint;
use Lettermint\Endpoints\SuppressionsEndpoint;
use Lettermint\Endpoints\TeamEndpoint;
use Lettermint\Endpoints\WebhooksEndpoint;

test('it exposes every documented api operation', function () {
    $operations = [
        ['sending', 'v1.sendMail', EmailEndpoint::class, 'send'],
        ['sending', 'v1.sendBatchMail', EmailEndpoint::class, 'sendBatch'],
        ['sending', 'v1.ping', EmailEndpoint::class, 'ping'],
        ['team', 'domain.index', DomainsEndpoint::class, 'list'],
        ['team', 'domain.store', DomainsEndpoint::class, 'create'],
        ['team', 'domain.show', DomainsEndpoint::class, 'retrieve'],
        ['team', 'domain.destroy', DomainsEndpoint::class, 'delete'],
        ['team', 'domain.verifyDnsRecords', DomainsEndpoint::class, 'verifyDnsRecords'],
        ['team', 'domain.verifySpecificDnsRecord', DomainsEndpoint::class, 'verifyDnsRecord'],
        ['team', 'domain.updateProjects', DomainsEndpoint::class, 'updateProjects'],
        ['team', 'v1.ping', ApiClient::class, 'ping'],
        ['team', 'v1.blockedFileTypes', ApiClient::class, 'blockedFileTypes'],
        ['team', 'message.index', MessagesEndpoint::class, 'list'],
        ['team', 'message.show', MessagesEndpoint::class, 'retrieve'],
        ['team', 'rescheduleMessage', MessagesEndpoint::class, 'reschedule'],
        ['team', 'cancelScheduledMessage', MessagesEndpoint::class, 'cancel'],
        ['team', 'message.events', MessagesEndpoint::class, 'events'],
        ['team', 'message.source', MessagesEndpoint::class, 'source'],
        ['team', 'message.html', MessagesEndpoint::class, 'html'],
        ['team', 'message.text', MessagesEndpoint::class, 'text'],
        ['team', 'project.index', ProjectsEndpoint::class, 'list'],
        ['team', 'project.store', ProjectsEndpoint::class, 'create'],
        ['team', 'project.show', ProjectsEndpoint::class, 'retrieve'],
        ['team', 'project.update', ProjectsEndpoint::class, 'update'],
        ['team', 'project.destroy', ProjectsEndpoint::class, 'delete'],
        ['team', 'project.rotateToken', ProjectsEndpoint::class, 'rotateToken'],
        ['team', 'route.index', ProjectsEndpoint::class, 'routes'],
        ['team', 'route.store', ProjectsEndpoint::class, 'createRoute'],
        ['team', 'route.show', RoutesEndpoint::class, 'retrieve'],
        ['team', 'route.update', RoutesEndpoint::class, 'update'],
        ['team', 'route.destroy', RoutesEndpoint::class, 'delete'],
        ['team', 'route.verifyInboundDomain', RoutesEndpoint::class, 'verifyInboundDomain'],
        ['team', 'stats.index', StatsEndpoint::class, 'retrieve'],
        ['team', 'suppression.index', SuppressionsEndpoint::class, 'list'],
        ['team', 'suppression.store', SuppressionsEndpoint::class, 'create'],
        ['team', 'suppression.destroy', SuppressionsEndpoint::class, 'delete'],
        ['team', 'team.show', TeamEndpoint::class, 'retrieve'],
        ['team', 'team.update', TeamEndpoint::class, 'update'],
        ['team', 'team.usage', TeamEndpoint::class, 'usage'],
        ['team', 'team.roles', TeamEndpoint::class, 'roles'],
        ['team', 'team.members', TeamEndpoint::class, 'members'],
        ['team', 'team.members.show', TeamEndpoint::class, 'member'],
        ['team', 'team.members.assignment.update', TeamEndpoint::class, 'updateMemberAssignment'],
        ['team', 'webhook.index', WebhooksEndpoint::class, 'list'],
        ['team', 'webhook.store', WebhooksEndpoint::class, 'create'],
        ['team', 'webhook.show', WebhooksEndpoint::class, 'retrieve'],
        ['team', 'webhook.update', WebhooksEndpoint::class, 'update'],
        ['team', 'webhook.destroy', WebhooksEndpoint::class, 'delete'],
        ['team', 'webhook.test', WebhooksEndpoint::class, 'test'],
        ['team', 'webhook.regenerateSecret', WebhooksEndpoint::class, 'regenerateSecret'],
        ['team', 'webhook.deliveries', WebhooksEndpoint::class, 'deliveries'],
        ['team', 'webhook.showDelivery', WebhooksEndpoint::class, 'delivery'],
    ];

    expect($operations)->toHaveCount(52);

    $missing = [];

    foreach ($operations as [$api, $operationId, $class, $method]) {
        if (! method_exists($class, $method)) {
            $missing[] = "$api operation $operationId on $class::$method";
        }
    }

    expect($missing)->toBe([]);
});

test('it uses concrete response classes for every documented endpoint response', function () {
    $typesSource = file_get_contents(__DIR__.'/../src/Types/ApiTypes.php');

    $endpointFiles = [
        __DIR__.'/../src/Client/ApiClient.php',
        __DIR__.'/../src/Endpoints/EmailEndpoint.php',
        __DIR__.'/../src/Endpoints/DomainsEndpoint.php',
        __DIR__.'/../src/Endpoints/MessagesEndpoint.php',
        __DIR__.'/../src/Endpoints/ProjectsEndpoint.php',
        __DIR__.'/../src/Endpoints/RoutesEndpoint.php',
        __DIR__.'/../src/Endpoints/StatsEndpoint.php',
        __DIR__.'/../src/Endpoints/SuppressionsEndpoint.php',
        __DIR__.'/../src/Endpoints/TeamEndpoint.php',
        __DIR__.'/../src/Endpoints/WebhooksEndpoint.php',
    ];

    $endpointSource = implode("\n", array_map(fn (string $file): string => file_get_contents($file), $endpointFiles));

    $expectedTypes = [
        'SendMailResponse',
        'SendBatchMailResponse',
        'BlockedFileTypesResponse',
        'DomainListResponse',
        'DomainResponse',
        'DeleteDomainResponse',
        'VerifyDnsRecordsResponse',
        'VerifyDnsRecordResponse',
        'UpdateDomainProjectsResponse',
        'MessageListResponse',
        'MessageResponse',
        'MessageEventsResponse',
        'ProjectListResponse',
        'CreateProjectResponse',
        'ProjectResponse',
        'UpdateProjectResponse',
        'DeleteProjectResponse',
        'RotateProjectTokenResponse',
        'ProjectRoutesResponse',
        'CreateRouteResponse',
        'RouteResponse',
        'UpdateRouteResponse',
        'DeleteRouteResponse',
        'VerifyInboundDomainResponse',
        'StatsResponse',
        'SuppressionListResponse',
        'CreateSuppressionResponse',
        'DeleteSuppressionResponse',
        'TeamResponse',
        'UpdateTeamResponse',
        'TeamUsageResponse',
        'TeamRolesResponse',
        'TeamMembersResponse',
        'TeamMembersShowResponse',
        'TeamMembersAssignmentUpdateResponse',
        'WebhookListResponse',
        'CreateWebhookResponse',
        'WebhookResponse',
        'UpdateWebhookResponse',
        'DeleteWebhookResponse',
        'TestWebhookResponse',
        'RegenerateWebhookSecretResponse',
        'WebhookDeliveriesResponse',
        'WebhookDeliveryResponse',
    ];

    foreach ($expectedTypes as $type) {
        $responseSource = file_get_contents(__DIR__."/../src/Responses/{$type}.php");

        expect($typesSource)->toContain("@phpstan-type {$type}");
        expect($responseSource)->toBeString()
            ->toContain('extends Resource')
            ->toContain('@property');
        expect($endpointSource)->toContain(": {$type}");
    }

    expect($endpointSource)->not->toContain('@phpstan-return ApiObject');
    expect($endpointSource)->not->toContain('@phpstan-return CursorPage');
    expect($endpointSource)->not->toContain('@phpstan-type SendResponse');
});

test('paginated php response types include concrete data item shapes', function () {
    $typesSource = file_get_contents(__DIR__.'/../src/Types/ApiTypes.php');

    $expectedDataTypes = [
        'DomainListResponse' => 'DomainListData',
        'MessageListResponse' => 'MessageListData',
        'MessageEventsResponse' => 'MessageEventData',
        'ProjectListResponse' => 'ProjectListData',
        'ProjectRoutesResponse' => 'RouteListData',
        'SuppressionListResponse' => 'SuppressedRecipientData',
        'TeamMembersResponse' => 'TeamMemberData',
        'WebhookListResponse' => 'WebhookListData',
        'WebhookDeliveriesResponse' => 'WebhookDeliveryListData',
    ];

    foreach ($expectedDataTypes as $responseType => $dataType) {
        expect($typesSource)->toContain("@phpstan-type {$responseType} array{");
        expect($typesSource)->toContain("data: list<{$dataType}>");
        expect($typesSource)->not->toContain("@phpstan-type {$responseType} CursorPage");
    }
});

test('generated php api types include current team schema additions', function () {
    $typesSource = file_get_contents(__DIR__.'/../src/Types/ApiTypes.php');

    expect($typesSource)
        ->toContain("'auto_replied'")
        ->toContain("'message.auto_replied'")
        ->toContain('redact_email_content: bool')
        ->toContain('redact_email_content?: bool|null')
        ->toContain('@phpstan-type UpdateRouteSettingsData array{')
        ->toContain('@phpstan-type UpdateRouteInboundSettingsData array{')
        ->toContain('generate_plaintext_fallback?: bool|null')
        ->toContain('tls?: TlsPolicy|null')
        ->toContain('short_token?: bool')
        ->toContain('@phpstan-type BlockedFileTypesResponse array{extensions: list<string>, mime_types: list<string>}')
        ->toContain('@phpstan-type BuiltInTeamRole')
        ->toContain('@phpstan-type TeamRoleData array{')
        ->toContain('included_volume: int')
        ->toContain('@phpstan-type UpdateTeamMemberAssignmentData array{')
        ->toContain('dkim_mode: DkimMode')
        ->toContain('source_message?: SuppressionSourceMessageData|null')
        ->toContain('spam_score?: float|int|null')
        ->toContain('scope: SuppressionScope');
});
