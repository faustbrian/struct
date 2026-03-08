<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Tests\Fixtures\Data\InstrumentedCastedListData;
use Tests\Fixtures\Data\InstrumentedSongCollectionData;
use Tests\Fixtures\Support\CollectionItemPropertyTracker;

describe('Collection item metadata', function (): void {
    beforeEach(function (): void {
        CollectionItemPropertyTracker::$calls = 0;
    });

    test('does not resolve collection item metadata within hydration loops for uncasted items', function (): void {
        InstrumentedSongCollectionData::create([
            'songs' => [
                ['title' => 'A', 'artist' => 'Artist A'],
                ['title' => 'B', 'artist' => 'Artist B'],
            ],
        ]);

        expect(CollectionItemPropertyTracker::$calls)->toBe(0);
    });

    test('does not resolve collection item metadata within hydration loops for casted items', function (): void {
        InstrumentedCastedListData::create([
            'numbers' => ['1', '2', '3'],
        ]);

        expect(CollectionItemPropertyTracker::$calls)->toBe(0);
    });

    test('does not resolve collection item metadata for uncasted collection item types', function (): void {
        $data = InstrumentedSongCollectionData::create([
            'songs' => [
                ['title' => 'A', 'artist' => 'Artist A'],
                ['title' => 'B', 'artist' => 'Artist B'],
            ],
        ]);

        CollectionItemPropertyTracker::$calls = 0;

        $data->toArray();

        expect(CollectionItemPropertyTracker::$calls)->toBe(0);
    });

    test('reuses collection item metadata within serialization loops with cast', function (): void {
        $data = InstrumentedCastedListData::create([
            'numbers' => ['1', '2', '3'],
        ]);

        CollectionItemPropertyTracker::$calls = 0;

        expect($data->toArray())
            ->toBe(['numbers' => ['1', '2', '3']])
            ->and(CollectionItemPropertyTracker::$calls)->toBe(0);
    });
});
