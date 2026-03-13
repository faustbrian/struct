<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\PhoneNumber\PhoneNumber;
use Cline\PostalCode\PostalCode;
use Tests\Fixtures\Data\PhoneNumberData;
use Tests\Fixtures\Data\PostalCodeData;

describe('Built-in package value object casts', function (): void {
    test('hydrates phone numbers from scalar and structured payloads', function (): void {
        $data = PhoneNumberData::create([
            'international' => '+358401234567',
            'local' => '202-555-0123',
        ]);

        expect($data->international)->toBeInstanceOf(PhoneNumber::class)
            ->and((string) $data->international)->toBe('+358401234567')
            ->and($data->local)->toBeInstanceOf(PhoneNumber::class)
            ->and((string) $data->local)->toBe('+12025550123')
            ->and($data->toArray())->toBe([
                'international' => '+358401234567',
                'local' => '+12025550123',
            ]);
    });

    test('hydrates postal codes from structured payloads and attribute metadata', function (): void {
        $data = PostalCodeData::create([
            'shipping' => [
                'postalCode' => '12345-6789',
                'country' => 'US',
            ],
            'billing' => 'K1A 0B1',
        ]);

        expect($data->shipping)->toBeInstanceOf(PostalCode::class)
            ->and((string) $data->shipping)->toBe('12345-6789')
            ->and($data->shipping->country())->toBe('US')
            ->and($data->billing)->toBeInstanceOf(PostalCode::class)
            ->and((string) $data->billing)->toBe('K1A 0B1')
            ->and($data->billing->country())->toBe('CA')
            ->and($data->toArray())->toBe([
                'shipping' => [
                    'postalCode' => '12345-6789',
                    'country' => 'US',
                ],
                'billing' => [
                    'postalCode' => 'K1A 0B1',
                    'country' => 'CA',
                ],
            ]);
    });
});
