<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support\CollectionCallbacks;

use Cline\Struct\Contracts\MapsCollectionItemsInterface;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class CountingPassthroughMapper implements MapsCollectionItemsInterface
{
    public static int $instances = 0;

    public function __construct()
    {
        ++self::$instances;
    }

    public function map(mixed $value, int|string $key): mixed
    {
        return $value;
    }
}
