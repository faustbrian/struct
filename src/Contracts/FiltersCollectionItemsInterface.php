<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

/**
 * Decides whether a collection item should remain in the collection.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface FiltersCollectionItemsInterface
{
    public function passes(mixed $value, int|string $key): bool;
}
