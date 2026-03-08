<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Carbon\CarbonImmutable;
use Cline\Struct\Exceptions\ImmutableDataCollectionException;
use Cline\Struct\Support\DataCollection;
use Illuminate\Support\Collection;
use Tests\Fixtures\Data\MappedUserData;
use Tests\Fixtures\Enums\UserStatus;

describe('DataCollection', function (): void {
    test('supports read-only collection accessors', function (): void {
        // Arrange
        $collection = new DataCollection([
            'first' => 'A',
            'second' => 'B',
        ]);

        // Act & Assert
        expect($collection->all())->toBe([
            'first' => 'A',
            'second' => 'B',
        ])->and($collection->first())->toBe('A')
            ->and($collection->count())->toBe(2)
            ->and(isset($collection['second']))->toBeTrue()
            ->and($collection['second'])->toBe('B')
            ->and($collection['missing'])->toBeNull();
    });

    test('converts into a laravel collection on demand', function (): void {
        // Arrange
        $collection = new DataCollection([
            'first' => 'A',
            'second' => 'B',
        ]);

        // Act
        $laravelCollection = $collection->toCollection();

        // Assert
        expect($laravelCollection)->toBeInstanceOf(Collection::class)
            ->and($laravelCollection->all())->toBe([
                'first' => 'A',
                'second' => 'B',
            ]);
    });

    test('throws when mutating the immutable collection', function (): void {
        // Arrange
        $collection = new DataCollection(['first' => 'A']);

        // Act & Assert
        expect(function () use ($collection): void {
            $collection['second'] = 'B';
        })->toThrow(ImmutableDataCollectionException::class);

        expect(function () use ($collection): void {
            unset($collection['first']);
        })->toThrow(ImmutableDataCollectionException::class);
    });

    test('serializes DTO items into plain arrays', function (): void {
        // Arrange
        $collection = new DataCollection([
            new MappedUserData(
                id: 1,
                fullName: 'Brian Faust',
                createdAt: CarbonImmutable::parse('2026-03-07T10:00:00+00:00'),
                status: UserStatus::Active,
            ),
        ]);

        // Act
        $serialized = $collection->toArray();

        // Assert
        expect($serialized)->toBe([
            [
                'id' => 1,
                'full_name' => 'Brian Faust',
                'created_at' => '2026-03-07T10:00:00+00:00',
                'status' => 'active',
                'tags' => [],
                'age' => null,
                'email' => null,
            ],
        ])->and($collection->jsonSerialize())->toBe($serialized);
    });

    test('returns scalar collections without creating serialization wrappers', function (): void {
        $collection = new DataCollection([
            'first' => 'A',
            'second' => 2,
            'third' => null,
            'fourth' => true,
        ]);

        expect($collection->toArray())->toBe([
            'first' => 'A',
            'second' => 2,
            'third' => null,
            'fourth' => true,
        ]);
    });

    test('treats nested plain values as a collection fast path', function (): void {
        $collection = new DataCollection([
            ['first' => 'A', 'second' => 2],
            (object) ['third' => true, 'fourth' => null],
        ]);
        $fastPath = new ReflectionMethod($collection, 'serializeFastPath');

        expect($fastPath->invoke($collection, null))->toBe([
            ['first' => 'A', 'second' => 2],
            ['third' => true, 'fourth' => null],
        ])
            ->and($collection->toArray())->toBe([
                ['first' => 'A', 'second' => 2],
                ['third' => true, 'fourth' => null],
            ]);
    });

    test('serializes dto-only collections through the shared context path', function (): void {
        $collection = new DataCollection([
            new MappedUserData(
                id: 1,
                fullName: 'Brian Faust',
                createdAt: CarbonImmutable::parse('2026-03-07T10:00:00+00:00'),
                status: UserStatus::Active,
            ),
            new MappedUserData(
                id: 2,
                fullName: 'Taylor Otwell',
                createdAt: CarbonImmutable::parse('2026-03-08T10:00:00+00:00'),
                status: UserStatus::Inactive,
            ),
        ]);

        expect($collection->toArray())->toHaveCount(2)
            ->and($collection->toArray()[0]['full_name'])->toBe('Brian Faust')
            ->and($collection->toArray()[1]['full_name'])->toBe('Taylor Otwell');
    });
});
