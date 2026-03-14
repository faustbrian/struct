<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support\CollectionCallbacks;

use Cline\Struct\Contracts\ComputesCollectionSortValueInterface;

use function is_string;
use function strlen;

final class StringLengthSortValue implements ComputesCollectionSortValueInterface
{
    public function sortValue(mixed $value, int|string $key): mixed
    {
        return is_string($value) ? strlen($value) : 0;
    }
}
