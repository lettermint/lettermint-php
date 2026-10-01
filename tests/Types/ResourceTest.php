<?php

use Lettermint\Objects\SuppressedRecipientData;
use Lettermint\Objects\SuppressionSourceMessageData;
use Lettermint\Objects\TeamMemberProjectAccessData;
use Lettermint\Resource;
use Lettermint\Responses\TeamMembersAssignmentUpdateResponse;
use Lettermint\Responses\TeamMembersShowResponse;

class TestChildResource extends Resource
{
    //
}

class TestParentResource extends Resource
{
    protected static array $casts = [
        'child' => TestChildResource::class,
        'children' => [TestChildResource::class],
    ];
}

test('resources expose attributes as properties and arrays', function () {
    $resource = new TestParentResource([
        'id' => 'parent-id',
        'child' => ['id' => 'child-id'],
        'children' => [
            ['id' => 'first-child-id'],
            ['id' => 'second-child-id'],
        ],
    ]);

    expect($resource->id)->toBe('parent-id')
        ->and($resource['id'])->toBe('parent-id')
        ->and($resource->child)->toBeInstanceOf(TestChildResource::class)
        ->and($resource->child->id)->toBe('child-id')
        ->and($resource->children[0])->toBeInstanceOf(TestChildResource::class)
        ->and($resource->children[1]->id)->toBe('second-child-id')
        ->and($resource->toArray())->toBe([
            'id' => 'parent-id',
            'child' => ['id' => 'child-id'],
            'children' => [
                ['id' => 'first-child-id'],
                ['id' => 'second-child-id'],
            ],
        ]);
});

test('suppression source messages remain resource objects', function () {
    $source = ['id' => 'message-id', 'available' => true, 'subject' => 'Test message', 'created_at' => null];
    $attributes = [
        'id' => 'suppression-id',
        'type' => 'email',
        'value' => 'recipient@example.com',
        'reason' => 'hard_bounce',
        'scope' => 'team',
        'applies_to' => 'all',
        'project_id' => null,
        'route_id' => null,
        'source_message' => $source,
        'created_at' => '2026-10-01T00:00:00Z',
    ];
    $resource = new SuppressedRecipientData($attributes);

    expect($resource->source_message)->toBeInstanceOf(SuppressionSourceMessageData::class)
        ->and($resource->source_message->getAttributes())->toBe($source)
        ->and($resource['source_message'])->toBeInstanceOf(SuppressionSourceMessageData::class)
        ->and($resource->toArray())->toBe($attributes);

    $attributes['source_message'] = null;
    $nullable = new SuppressedRecipientData($attributes);
    expect($nullable->source_message)->toBeNull()
        ->and($nullable['source_message'])->toBeNull()
        ->and($nullable->toArray())->toBe($attributes);
});

test('team member project access remains a resource object', function (string $responseClass) {
    $access = ['scope' => 'selected', 'projects' => [['id' => 'project-id', 'name' => 'Test project']]];
    $attributes = [
        'id' => 'member-id',
        'name' => 'Test member',
        'email' => 'member@example.com',
        'role' => ['id' => 'role-id', 'name' => 'Admin'],
        'project_access' => $access,
        'joined_at' => null,
    ];
    $resource = new $responseClass($attributes);

    expect($resource->project_access)->toBeInstanceOf(TeamMemberProjectAccessData::class)
        ->and($resource->project_access->getAttributes())->toBe($access)
        ->and($resource['project_access'])->toBeInstanceOf(TeamMemberProjectAccessData::class)
        ->and($resource->toArray())->toBe($attributes);
})->with([TeamMembersShowResponse::class, TeamMembersAssignmentUpdateResponse::class]);
