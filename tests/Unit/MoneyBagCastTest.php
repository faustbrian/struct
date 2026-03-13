<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Money\MoneyBag;
use Cline\Money\RationalMoney;
use Tests\Fixtures\Data\MoneyBagData;

describe('Built-in money bag casts', function (): void {
    test('hydrates rational money and money bags from structured payloads', function (): void {
        $data = MoneyBagData::create([
            'totals' => [
                [
                    'amount' => '12.50',
                    'currency' => 'USD',
                ],
                [
                    'amount' => '6/8',
                    'currency' => 'EUR',
                ],
            ],
            'exactAmount' => [
                'amount' => '10/4',
                'currency' => 'USD',
            ],
        ]);

        expect($data->totals)->toBeInstanceOf(MoneyBag::class)
            ->and((string) $data->totals->getMoney('USD'))->toBe('USD 25/2')
            ->and((string) $data->totals->getMoney('EUR'))->toBe('EUR 3/4')
            ->and($data->exactAmount)->toBeInstanceOf(RationalMoney::class)
            ->and((string) $data->exactAmount)->toBe('USD 5/2')
            ->and($data->toArray())->toBe([
                'totals' => [
                    [
                        'amount' => '25/2',
                        'currency' => 'USD',
                    ],
                    [
                        'amount' => '3/4',
                        'currency' => 'EUR',
                    ],
                ],
                'exactAmount' => [
                    'amount' => '5/2',
                    'currency' => 'USD',
                ],
            ]);
    });
});
