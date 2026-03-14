<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\Collections;

use Cline\Struct\Contracts\TransformsCollectionValueInterface;
use Cline\Struct\Contracts\TransformsLaravelCollectionValueInterface;
use Illuminate\Support\Collection;

/**
 * Base attribute for deterministic collection transforms.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
abstract readonly class AbstractCollectionTransformer implements TransformsCollectionValueInterface, TransformsLaravelCollectionValueInterface
{
    public function supportsLists(): bool
    {
        return true;
    }

    public function transformCollection(Collection $items): Collection
    {
        return new Collection($this->transform($items->all()));
    }
}
