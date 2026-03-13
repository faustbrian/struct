<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\PhoneNumber\PhoneNumber;
use Tests\Fixtures\Data\PhoneNumberData;

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
});
