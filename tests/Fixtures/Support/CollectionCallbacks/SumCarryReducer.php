<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support\CollectionCallbacks;

use Cline\Struct\Contracts\ReducesCollectionItemsInterface;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class SumCarryReducer implements ReducesCollectionItemsInterface
{
    public function reduce(mixed $carry, mixed $value, int|string $key): mixed
    {
        return (int) $carry + (int) $value;
    }
}
