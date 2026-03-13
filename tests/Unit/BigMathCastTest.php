<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Math\BigDecimal;
use Cline\Math\BigInteger;
use Cline\Math\BigNumber;
use Cline\Math\BigRational;
use Tests\Fixtures\Data\BigMathData;

describe('Built-in big number casts', function (): void {
    test('hydrates math value objects and serializes them back to strings', function (): void {
        $data = BigMathData::create([
            'integer' => '12345678901234567890',
            'decimal' => 12.345,
            'rational' => '6/8',
            'number' => '1/3',
        ]);

        expect($data->integer)->toBeInstanceOf(BigInteger::class)
            ->and((string) $data->integer)->toBe('12345678901234567890')
            ->and($data->decimal)->toBeInstanceOf(BigDecimal::class)
            ->and((string) $data->decimal)->toBe('12.345')
            ->and($data->rational)->toBeInstanceOf(BigRational::class)
            ->and((string) $data->rational)->toBe('3/4')
            ->and($data->number)->toBeInstanceOf(BigNumber::class)
            ->and($data->number)->toBeInstanceOf(BigRational::class)
            ->and((string) $data->number)->toBe('1/3')
            ->and($data->toArray())->toBe([
                'integer' => '12345678901234567890',
                'decimal' => '12.345',
                'rational' => '3/4',
                'number' => '1/3',
            ]);
    });
});
