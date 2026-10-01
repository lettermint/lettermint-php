<?php

use Lettermint\Client\ApiClient;
use Lettermint\Client\HttpClient;
use Lettermint\Endpoints\ProjectsEndpoint;
use Lettermint\Objects\ReportForwardingResource;
use Lettermint\Responses\CreateProjectResponse;
use Lettermint\Responses\DeleteSuppressionResponse;

test('it maps report forwarding and accepts an empty delete response', function () {
    $http = Mockery::mock(HttpClient::class);
    $path = '/v1/projects/project%2Fid/report-forwarding';
    $data = ['destination' => null, 'verified' => false, 'verified_at' => null];
    $http->shouldReceive('get')->once()->with($path, [])->andReturn(['data' => $data]);
    $http->shouldReceive('put')->once()->with($path, ['destination' => 'reports@example.com'], [])->andReturn(['data' => $data]);
    $http->shouldReceive('post')->once()->with($path.'/verify', ['code' => '123456'], [])->andReturn(['data' => $data]);
    $http->shouldReceive('post')->once()->with($path.'/resend-code', [], [])->andReturn(['data' => $data]);
    $http->shouldReceive('delete')->once()->with($path, [])->andReturn(null);
    $projects = new ProjectsEndpoint($http);
    $result = $projects->retrieveReportForwarding('project/id');
    expect($result->data)->toBeInstanceOf(ReportForwardingResource::class)
        ->and($result->data->destination)->toBeNull()
        ->and($result->data->verified)->toBeFalse();
    $projects->updateReportForwarding('project/id', ['destination' => 'reports@example.com']);
    $projects->verifyReportForwarding('project/id', ['code' => '123456']);
    $projects->resendReportForwardingCode('project/id');
    $projects->deleteReportForwarding('project/id');
});

test('it posts analytics and keeps typed request aliases', function () {
    $payload = ['metrics' => ['accepted', 'delivered'], 'include' => ['summary'], 'interval' => 'day'];
    $response = ['data' => ['summary' => ['current' => ['accepted' => 12, 'delivery_rate' => null]]], 'meta' => ['timezone' => 'UTC'], 'pagination' => ['total_groups' => 0, 'returned_groups' => 0, 'next_cursor' => null, 'truncated' => false]];
    $http = Mockery::mock(HttpClient::class);
    $http->shouldReceive('post')->once()->with('/v1/analytics', $payload, [])->andReturn($response);
    $api = new ApiClient('team-token');
    (new ReflectionProperty(ApiClient::class, 'httpClient'))->setValue($api, $http);
    expect($api->analytics($payload)->toArray())->toBe($response);
});

test('it keeps project tokens and both suppression deletion results', function () {
    $project = new CreateProjectResponse(['data' => ['id' => 'project-1'], 'message' => 'Created', 'api_token' => 'project-token']);
    expect($project->api_token)->toBe('project-token');
    $deleted = new DeleteSuppressionResponse(['success' => true, 'status' => 'removed', 'message' => 'Deleted', 'confidence' => 0.9]);
    $review = new DeleteSuppressionResponse(['success' => true, 'status' => 'review_ticket_exists', 'message' => 'Review', 'ticket_identifier' => 'ticket-1']);
    expect($deleted->confidence)->toBe(0.9)->and($review->ticket_identifier)->toBe('ticket-1');
});
