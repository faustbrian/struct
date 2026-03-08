<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Tests\Fixtures\Data\LazySerializationData;

describe('LazySerialization', function (): void {
    beforeEach(function (): void {
        $this->basePayload = [
            'id' => 1,
            'name' => 'Brian',
            'posts' => [],
        ];
    });

    describe('Happy Paths', function (): void {
        test('omits lazy properties by default and includes requested fields', function (): void {
            // Arrange
            $payload = $this->basePayload;
            $payload['bio'] = 'Builder';
            $payload['location'] = 'Helsinki';

            // Act
            $dto = LazySerializationData::create($payload);

            // Assert
            expect($dto->toArray())->toBe([
                'id' => 1,
                'name' => 'Brian',
                'posts' => [],
            ])->and($dto->toArray(include: ['bio']))->toBe([
                'id' => 1,
                'name' => 'Brian',
                'bio' => 'Builder',
                'posts' => [],
            ]);
        });

        test('includes lazy groups and context-based lazy properties', function (): void {
            // Arrange
            $payload = $this->basePayload;
            $payload['location'] = 'Helsinki';
            $payload['adminNotes'] = 'Restricted';

            // Act
            $dto = LazySerializationData::create($payload);

            // Assert
            expect($dto->toArray(groups: ['details']))->toBe([
                'id' => 1,
                'name' => 'Brian',
                'location' => 'Helsinki',
                'posts' => [],
            ])->and($dto->toArray(context: ['is_admin' => true]))->toBe([
                'id' => 1,
                'name' => 'Brian',
                'adminNotes' => 'Restricted',
                'posts' => [],
            ]);
        });

        test('resolves lazy values and computed properties when included', function (): void {
            // Arrange
            $payload = $this->basePayload;

            // Act
            $dto = LazySerializationData::create($payload);

            // Assert
            expect($dto->toArray(include: ['analytics', 'displayName'], context: ['viewer' => 'admin']))->toBe([
                'id' => 1,
                'name' => 'Brian',
                'analytics' => [
                    'viewer' => 'admin',
                    'property' => 'analytics',
                ],
                'displayName' => 'Brian profile',
                'posts' => [],
            ]);
        });

        test('keeps nested include paths through collections and nested dto properties', function (): void {
            // Arrange
            $payload = $this->basePayload;
            $payload['posts'] = [
                [
                    'title' => 'Post',
                    'author' => [
                        'name' => 'Rick',
                        'profile' => [
                            'handle' => '@rick',
                            'bio' => 'Singer',
                        ],
                    ],
                ],
            ];

            // Act
            $dto = LazySerializationData::create($payload);

            // Assert
            expect($dto->toArray(include: ['posts.author.profile.bio']))->toBe([
                'id' => 1,
                'name' => 'Brian',
                'posts' => [
                    [
                        'title' => 'Post',
                        'author' => [
                            'name' => 'Rick',
                            'profile' => [
                                'handle' => '@rick',
                                'bio' => 'Singer',
                            ],
                        ],
                    ],
                ],
            ]);
        });

        test('supports fluent serializer include and json output', function (): void {
            // Arrange
            $payload = $this->basePayload;
            $payload['bio'] = 'Builder';

            // Act
            $dto = LazySerializationData::create($payload);
            $serializer = $dto->serializer()->include('bio');

            // Assert
            expect($serializer->toArray())->toBe([
                'id' => 1,
                'name' => 'Brian',
                'bio' => 'Builder',
                'posts' => [],
            ])->and($serializer->toJson())->toBe(
                json_encode([
                    'id' => 1,
                    'name' => 'Brian',
                    'bio' => 'Builder',
                    'posts' => [],
                ], \JSON_THROW_ON_ERROR),
            );
        });
    });

    describe('Sad Paths', function (): void {
        // No sad-path cases for this suite.
    });

    describe('Edge Cases', function (): void {
        // Reserved for boundary and unusual serialization payloads.
    });

    describe('Regressions', function (): void {
        // Reserved for bug-linked regression scenarios.
    });
});
