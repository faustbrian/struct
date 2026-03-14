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
 * Declares a side-effectful callback for a Laravel collection.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface TapsCollectionValueInterface
{
    /**
     * @param Collection<array-key, mixed> $items
     */
    public function tap(Collection $items): void;
}
