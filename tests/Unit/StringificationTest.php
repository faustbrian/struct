<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Tests\Fixtures\Data\CountedStringifiedData;
use Tests\Fixtures\Support\CountingStringifier;

describe('stringification', function (): void {
    test('reuses the configured stringifier instance', function (): void {
        CountingStringifier::$instances = 0;
        $data = CountedStringifiedData::create(['value' => 'A']);

        expect((string) $data)->toBe('{"value":"A"}')
            ->and((string) $data)->toBe('{"value":"A"}')
            ->and(CountingStringifier::$instances)->toBe(1);
    });
});
