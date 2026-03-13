<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Money\Context\CustomContext;
use Cline\Money\Money;
use Tests\Fixtures\Data\AttributedMoneyData;
use Tests\Fixtures\Data\MoneyData;

describe('Built-in money casts', function (): void {
    test('hydrates money properties from structured payloads and serializes them back to arrays', function (): void {
        $data = MoneyData::create([
            'amount' => [
                'amount' => '12.345',
                'currency' => 'USD',
                'context' => [
                    'type' => 'custom',
                    'scale' => 3,
                    'step' => 1,
                ],
            ],
        ]);

        expect($data->amount)->toBeInstanceOf(Money::class)
            ->and((string) $data->amount)->toBe('USD 12.345')
            ->and($data->amount->getContext())->toBeInstanceOf(CustomContext::class)
            ->and($data->toArray())->toBe([
                'amount' => [
                    'amount' => '12.345',
                    'currency' => 'USD',
                    'context' => [
                        'type' => 'custom',
                        'scale' => 3,
                        'step' => 1,
                    ],
                ],
            ]);
    });

    test('hydrates money properties from scalar values when currency is provided by attribute metadata', function (): void {
        $data = AttributedMoneyData::create([
            'amount' => '12.50',
            'minorAmount' => 1_234,
        ]);

        expect($data->amount)->toBeInstanceOf(Money::class)
            ->and((string) $data->amount)->toBe('USD 12.50')
            ->and($data->minorAmount)->toBeInstanceOf(Money::class)
            ->and((string) $data->minorAmount)->toBe('JPY 1234')
            ->and($data->toArray())->toBe([
                'amount' => [
                    'amount' => '12.50',
                    'currency' => 'USD',
                    'context' => [
                        'type' => 'default',
                    ],
                ],
                'minorAmount' => [
                    'amount' => '1234',
                    'currency' => 'JPY',
                    'context' => [
                        'type' => 'default',
                    ],
                ],
            ]);
    });
});
