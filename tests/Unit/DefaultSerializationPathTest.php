<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Tests\Fixtures\Data\ObservedDefaultSerializationParentData;
use Tests\Fixtures\Data\ObservedDefaultSerializationPathData;
use Tests\Fixtures\Support\DefaultSerializationPathTracker;

describe('Default Serialization Path', function (): void {
    beforeEach(function (): void {
        DefaultSerializationPathTracker::reset();
    });

    test('uses the dedicated default path for plain toArray calls', function (): void {
        $dto = ObservedDefaultSerializationPathData::create([
            'value' => 'hello',
        ]);

        expect($dto->toArray())->toBe(['value' => 'hello'])
            ->and(DefaultSerializationPathTracker::$defaultCalls)->toBe(1)
            ->and(DefaultSerializationPathTracker::$genericCalls)->toBe(0);
    });

    test('falls back to the generic path when serialization projections are customized', function (): void {
        $dto = ObservedDefaultSerializationPathData::create([
            'value' => 'hello',
        ]);

        expect($dto->toArray(include: ['value']))->toBe(['value' => 'hello'])
            ->and(DefaultSerializationPathTracker::$defaultCalls)->toBe(0)
            ->and(DefaultSerializationPathTracker::$genericCalls)->toBe(1);
    });

    test('uses the dedicated default path for nested dto properties', function (): void {
        $dto = ObservedDefaultSerializationParentData::create([
            'child' => [
                'value' => 'hello',
            ],
        ]);

        expect($dto->toArray())->toBe([
            'child' => [
                'value' => 'hello',
            ],
        ])->and(DefaultSerializationPathTracker::$defaultCalls)->toBe(1)
            ->and(DefaultSerializationPathTracker::$genericCalls)->toBe(0);
    });
});
