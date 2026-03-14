<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

use Illuminate\Support\Collection;

/**
 * Declares a processor that accepts and returns a Laravel collection.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface PipesCollectionValueInterface
{
    /**
     * @param  Collection<array-key, mixed> $items
     * @return Collection<array-key, mixed>
     */
    public function pipe(Collection $items): Collection;
}
