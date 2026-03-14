<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

/**
 * Reduces a collection into a single value.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface ReducesCollectionItemsInterface
{
    public function reduce(mixed $carry, mixed $value, int|string $key): mixed;
}
