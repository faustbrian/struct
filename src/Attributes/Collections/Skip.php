<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\Collections;

use Attribute;
use Cline\Struct\Contracts\TransformsLaravelCollectionValueInterface;
use Cline\Struct\Contracts\TransformsLazyCollectionValueInterface;
use Cline\Struct\Support\CreationContext;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Skip implements TransformsLaravelCollectionValueInterface, TransformsLazyCollectionValueInterface
{
    public function __construct(
        public int $count,
    ) {}

    public function transformCollection(Collection $items, ?CreationContext $context = null): Collection
    {
        return $items->skip($this->count);
    }

    public function transformLazyCollection(LazyCollection $items, ?CreationContext $context = null): LazyCollection
    {
        return $items->skip($this->count);
    }
}
