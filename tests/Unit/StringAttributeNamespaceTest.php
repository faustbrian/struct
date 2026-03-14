<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Attributes\Strings\Trim;
use Cline\Struct\Attributes\Strings\Uuid;

test('string attributes use the strings namespace with backward-compatible aliases', function (): void {
    expect(class_exists(Trim::class))->toBeTrue()
        ->and(class_exists(Uuid::class))->toBeTrue()
        ->and(
            new Cline\Struct\Attributes\Trim(),
        )->toBeInstanceOf(Trim::class)
        ->and(
            new Cline\Struct\Attributes\Uuid(),
        )->toBeInstanceOf(Uuid::class);
});
