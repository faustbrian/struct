<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Support;

/**
 * Marks a data object property as intentionally missing from input.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Optional
{
    /**
     * Create the sentinel instance used for missing values.
     */
    public static function missing(): self
    {
        return new self();
    }

    /**
     * Determine whether the sentinel represents a missing value.
     */
    public function isMissing(): bool
    {
        return true;
    }
}
