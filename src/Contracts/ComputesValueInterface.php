<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

/**
 * Computes the value of a property marked as computed.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface ComputesValueInterface
{
    /**
     * Compute a value from the already-known property set.
     *
     * @param array<string, mixed> $properties
     */
    public function compute(array $properties): mixed;
}
