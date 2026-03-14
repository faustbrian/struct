<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support\CollectionCallbacks;

use Cline\Struct\Contracts\ChunksCollectionItemsInterface;
use Illuminate\Support\Collection;

use function mb_strtolower;
use function mb_substr;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class AlphaChunkBoundary implements ChunksCollectionItemsInterface
{
    public function shouldStartNewChunk(mixed $value, int|string $key, Collection $chunk): bool
    {
        if ($chunk->isEmpty()) {
            return false;
        }

        return mb_substr(mb_strtolower((string) $chunk->last()), 0, 1) !== mb_substr(mb_strtolower((string) $value), 0, 1);
    }
}
