<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

/**
 * Maps a collection item into a replacement value.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface MapsCollectionItemsInterface
{
    public function map(mixed $value, int|string $key): mixed;
}
