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
 * Decides whether an item should start a new chunk.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface ChunksCollectionItemsInterface
{
    /**
     * @param Collection<array-key, mixed> $chunk
     */
    public function shouldStartNewChunk(mixed $value, int|string $key, Collection $chunk): bool;
}
