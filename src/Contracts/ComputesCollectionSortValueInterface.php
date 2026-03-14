<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

/**
 * Computes the sort value for a collection item.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface ComputesCollectionSortValueInterface
{
    public function sortValue(mixed $value, int|string $key): mixed;
}
