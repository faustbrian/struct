<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Numerus\Numerus;
use Tests\Fixtures\Data\NumericAttributeData;
use Tests\Fixtures\Data\NumerusData;

describe('Built-in numeric casts', function (): void {
    test('hydrates and serializes numeric normalization attributes', function (): void {
        $data = NumericAttributeData::create([
            'rounded' => '12.345',
            'roundedUp' => '12.341',
            'roundedDown' => '12.349',
            'roundedHalfUp' => '12.345',
            'roundedHalfDown' => '12.345',
            'roundedHalfEven' => '12.345',
            'roundedCeiling' => '-12.341',
            'roundedFloor' => '-12.341',
            'ceiled' => '12.1',
            'floored' => '12.9',
            'clamped' => 25,
            'absolute' => -15,
        ]);

        expect($data->rounded)->toBe(12.35)
            ->and($data->roundedUp)->toBe(12.35)
            ->and($data->roundedDown)->toBe(12.34)
            ->and($data->roundedHalfUp)->toBe(12.35)
            ->and($data->roundedHalfDown)->toBe(12.34)
            ->and($data->roundedHalfEven)->toBe(12.34)
            ->and($data->roundedCeiling)->toBe(-12.34)
            ->and($data->roundedFloor)->toBe(-12.35)
            ->and($data->ceiled)->toBe(13)
            ->and($data->floored)->toBe(12)
            ->and($data->clamped)->toBe(20)
            ->and($data->absolute)->toBe(15)
            ->and($data->toArray())->toBe([
                'rounded' => 12.35,
                'roundedUp' => 12.35,
                'roundedDown' => 12.34,
                'roundedHalfUp' => 12.35,
                'roundedHalfDown' => 12.34,
                'roundedHalfEven' => 12.34,
                'roundedCeiling' => -12.34,
                'roundedFloor' => -12.35,
                'ceiled' => 13,
                'floored' => 12,
                'clamped' => 20,
                'absolute' => 15,
            ]);
    });

    test('hydrates numerus properties and serializes them back to scalar values', function (): void {
        $data = NumerusData::create([
            'amount' => '12.50',
        ]);

        expect($data->amount)->toBeInstanceOf(Numerus::class)
            ->and($data->amount->toFloat())->toBe(12.5)
            ->and($data->toArray())->toBe([
                'amount' => 12.5,
            ]);
    });
});
