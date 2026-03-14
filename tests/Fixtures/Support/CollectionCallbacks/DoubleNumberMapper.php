<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support\CollectionCallbacks;

use Cline\Struct\Contracts\MapsCollectionItemsInterface;

use function is_int;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class DoubleNumberMapper implements MapsCollectionItemsInterface
{
    public function map(mixed $value, int|string $key): mixed
    {
        return is_int($value) ? $value * 2 : $value;
    }
}
