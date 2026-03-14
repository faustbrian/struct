<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

/**
 * Reduces a collection into a tuple of values.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface ReducesCollectionItemsSpreadInterface
{
    /**
     * @param  array<int, mixed> $carry
     * @return array<int, mixed>
     */
    public function reduce(array $carry, mixed $value): array;
}
