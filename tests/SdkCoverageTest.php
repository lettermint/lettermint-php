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
        ['team', 'message.index', MessagesEndpoint::class, 'list'],
        ['team', 'message.show', MessagesEndpoint::class, 'retrieve'],
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
        ['team', 'project.updateMembers', ProjectsEndpoint::class, 'updateMembers'],
        ['team', 'project.addMember', ProjectsEndpoint::class, 'addMember'],
        ['team', 'project.removeMember', ProjectsEndpoint::class, 'removeMember'],
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
        ['team', 'team.members', TeamEndpoint::class, 'members'],
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

    expect($operations)->toHaveCount(49);

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
        'UpdateProjectMembersResponse',
        'ProjectMemberResponse',
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
        'TeamMembersResponse',
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
