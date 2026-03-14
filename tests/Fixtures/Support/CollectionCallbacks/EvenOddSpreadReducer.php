<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support\CollectionCallbacks;

use Cline\Struct\Contracts\ReducesCollectionItemsSpreadInterface;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class EvenOddSpreadReducer implements ReducesCollectionItemsSpreadInterface
{
    public function reduce(array $carry, mixed $value): array
    {
        [$even, $odd] = $carry;

        if (((int) $value % 2) === 0) {
            return [$even + (int) $value, $odd];
        }

        return [$even, $odd + (int) $value];
    }
}
