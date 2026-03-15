<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Tests\Fixtures\Casts\ContextualValueCast;
use Tests\Fixtures\Data\ContextualCastData;
use Tests\Fixtures\Data\ContextualCollectionTransformData;
use Tests\Fixtures\Data\ContextualStringTransformData;

describe('Contextual hydration', function (): void {
    beforeEach(function (): void {
        ContextualValueCast::reset();
    });

    test('passes raw input and resolved sibling values to contextual casts', function (): void {
        $data = ContextualCastData::create([
            'mode' => 'upper',
            'prefix' => 'raw:',
            'value' => 'hello',
        ]);

        expect($data->value)->toBe('HELLO')
            ->and(ContextualValueCast::$observations)->toHaveCount(1)
            ->and(ContextualValueCast::$observations[0])->toMatchArray([
                'dataClass' => ContextualCastData::class,
                'property' => 'value',
                'rawInput' => [
                    'mode' => 'upper',
                    'prefix' => 'raw:',
                    'value' => 'hello',
                ],
                'resolvedProperties' => [
                    'mode' => 'upper',
                    'prefix' => 'raw:',
                ],
            ]);
    });

    test('does not leak per-hydration context across reused cast instances', function (): void {
        $upper = ContextualCastData::create([
            'mode' => 'upper',
            'prefix' => 'ignored:',
            'value' => 'Mixed',
        ]);

        $lower = ContextualCastData::create([
            'mode' => 'lower',
            'prefix' => 'raw:',
            'value' => 'Mixed',
        ]);

        expect($upper->value)->toBe('MIXED')
            ->and($lower->value)->toBe('raw:Mixed')
            ->and(ContextualValueCast::$observations)->toHaveCount(2)
            ->and(ContextualValueCast::$observations[0]['resolvedProperties'])->toBe([
                'mode' => 'upper',
                'prefix' => 'ignored:',
            ])
            ->and(ContextualValueCast::$observations[1]['resolvedProperties'])->toBe([
                'mode' => 'lower',
                'prefix' => 'raw:',
            ]);
    });

    test('passes resolved sibling values to hydration-time collection transforms', function (): void {
        $data = ContextualCollectionTransformData::create([
            'prefix' => 'tag:',
            'items' => ['first', 'second'],
        ]);

        expect($data->items)->toBe([
            'tag:first',
            'tag:second',
        ]);
    });

    test('passes resolved sibling values to contextual string transforms', function (): void {
        $data = ContextualStringTransformData::create([
            'prefix' => 'tag:',
            'value' => 'first',
        ]);

        expect($data->value)->toBe('tag:first');
    });
});
