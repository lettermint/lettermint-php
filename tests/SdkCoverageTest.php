<?php

use Lettermint\Client\SendingClient;
use Lettermint\Client\TeamClient;
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
        ['sending', 'v1.ping', SendingClient::class, 'ping'],
        ['team', 'domain.index', DomainsEndpoint::class, 'list'],
        ['team', 'domain.store', DomainsEndpoint::class, 'create'],
        ['team', 'domain.show', DomainsEndpoint::class, 'retrieve'],
        ['team', 'domain.destroy', DomainsEndpoint::class, 'delete'],
        ['team', 'domain.verifyDnsRecords', DomainsEndpoint::class, 'verifyDnsRecords'],
        ['team', 'domain.verifySpecificDnsRecord', DomainsEndpoint::class, 'verifyDnsRecord'],
        ['team', 'domain.updateProjects', DomainsEndpoint::class, 'updateProjects'],
        ['team', 'v1.ping', TeamClient::class, 'ping'],
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
