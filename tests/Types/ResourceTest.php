<?php

use Lettermint\Resource;

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
