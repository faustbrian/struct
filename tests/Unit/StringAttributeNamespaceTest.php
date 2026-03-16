<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Attributes\Strings\Trim;
use Cline\Struct\Attributes\Strings\Uuid;

test('string attributes use the strings namespace', function (): void {
    expect(class_exists(Trim::class))->toBeTrue()
        ->and(class_exists(Uuid::class))->toBeTrue()
        ->and(
            new Trim()
        )->toBeInstanceOf(Trim::class)
        ->and(
            new Uuid()
        )->toBeInstanceOf(Uuid::class);
});
