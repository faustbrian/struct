<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Tests\Fixtures\Data\ObservedCollectionTransformData;
use Tests\Fixtures\Data\ObservedCollectionAttributeScanData;
use Tests\Fixtures\Support\ObservedCollectionAttributeScans;
use Tests\Fixtures\Support\ObservedCollectionTransformInstantiations;

describe('collection attribute caching', function (): void {
    test('skips runtime collection attribute scans when no transforms exist', function (): void {
        ObservedCollectionAttributeScanData::create([
            'items' => ['warmup'],
        ]);

        ObservedCollectionAttributeScanData::reset();

        $data = ObservedCollectionAttributeScanData::create([
            'items' => ['a', 'b', 'c'],
        ]);

        expect($data->items->all())->toBe(['a', 'b', 'c'])
            ->and(ObservedCollectionAttributeScans::$count)->toBe(0);
    });

    test('instantiates collection transforms once per hydration operation', function (): void {
        ObservedCollectionTransformData::create([
            'items' => ['warmup'],
        ]);

        ObservedCollectionTransformData::reset();

        $data = ObservedCollectionTransformData::create([
            'items' => ['a', 'b', 'c'],
        ]);

        expect($data->items)->toBe(['a', 'b', 'c'])
            ->and(ObservedCollectionTransformInstantiations::$count)->toBe(1);
    });
});
