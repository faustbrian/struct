<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Tests\Fixtures\Data\ObservedCollectionTransformData;
use Tests\Fixtures\Support\ObservedCollectionTransformInstantiations;

describe('collection attribute caching', function (): void {
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
