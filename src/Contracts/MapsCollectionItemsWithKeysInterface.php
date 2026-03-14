<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

/**
 * Maps a collection item into one or more keyed values.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface MapsCollectionItemsWithKeysInterface
{
    /**
     * @return array<array-key, mixed>
     */
    public function mapWithKeys(mixed $value, int|string $key): array;
}
