<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support\CollectionCallbacks;

use Cline\Struct\Contracts\ComparesCollectionValuesInterface;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class NumericStringComparator implements ComparesCollectionValuesInterface
{
    public function compare(mixed $left, mixed $right): int
    {
        return (int) $left <=> (int) $right;
    }
}
