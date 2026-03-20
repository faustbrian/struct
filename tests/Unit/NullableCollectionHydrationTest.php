<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Tests\Fixtures\Data\NullableCollectionData;

describe('nullable collection hydration', function (): void {
    test('hydrates collection wrappers when nullable properties receive iterable input', function (): void {
        $dto = NullableCollectionData::create([
            'numbers' => ['1', 2, '3'],
            'songs' => [
                'first' => ['title' => 'A', 'artist' => 'Artist A'],
                'second' => ['title' => 'B', 'artist' => 'Artist B'],
            ],
            'lazyNumbers' => ['1', 2, '3'],
            'lazySongs' => [
                'first' => ['title' => 'A', 'artist' => 'Artist A'],
                'second' => ['title' => 'B', 'artist' => 'Artist B'],
            ],
            'collectionNumbers' => ['1', 2, '3'],
            'lazyCollectionNumbers' => ['1', 2, '3'],
        ]);

        expect($dto->numbers?->toArray())->toBe([1, 2, 3])
            ->and($dto->songs?->toArray())->toBe([
                'first' => ['title' => 'A', 'artist' => 'Artist A'],
                'second' => ['title' => 'B', 'artist' => 'Artist B'],
            ])
            ->and($dto->lazyNumbers?->toArray())->toBe([1, 2, 3])
            ->and($dto->lazySongs?->toArray())->toBe([
                'first' => ['title' => 'A', 'artist' => 'Artist A'],
                'second' => ['title' => 'B', 'artist' => 'Artist B'],
            ])
            ->and($dto->collectionNumbers?->all())->toBe([1, 2, 3])
            ->and($dto->lazyCollectionNumbers?->all())->toBe([1, 2, 3]);
    });

    test('preserves explicit null for nullable collection wrappers', function (): void {
        $dto = NullableCollectionData::create([
            'numbers' => null,
            'songs' => null,
            'lazyNumbers' => null,
            'lazySongs' => null,
            'collectionNumbers' => null,
            'lazyCollectionNumbers' => null,
        ]);

        expect($dto->numbers)->toBeNull()
            ->and($dto->songs)->toBeNull()
            ->and($dto->lazyNumbers)->toBeNull()
            ->and($dto->lazySongs)->toBeNull()
            ->and($dto->collectionNumbers)->toBeNull()
            ->and($dto->lazyCollectionNumbers)->toBeNull();
    });

    test('preserves missing nullable collection wrappers as null', function (): void {
        $dto = NullableCollectionData::create([]);

        expect($dto->numbers)->toBeNull()
            ->and($dto->songs)->toBeNull()
            ->and($dto->lazyNumbers)->toBeNull()
            ->and($dto->lazySongs)->toBeNull()
            ->and($dto->collectionNumbers)->toBeNull()
            ->and($dto->lazyCollectionNumbers)->toBeNull();
    });
});
