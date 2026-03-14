<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\Collections;

use Attribute;
use Cline\Struct\Contracts\ComputesCollectionSortValueInterface;
use Cline\Struct\Support\CreationContext;
use Illuminate\Support\Collection;

use const SORT_REGULAR;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class SortBy extends AbstractCollectionCallbackTransformer
{
    public function __construct(
        string $callback,
        public bool $descending = false,
        public int $options = SORT_REGULAR,
    ) {
        parent::__construct($callback);
    }

    public function transformCollection(Collection $items, ?CreationContext $context = null): Collection
    {
        /** @var ComputesCollectionSortValueInterface $callback */
        $callback = $this->resolveCallback(ComputesCollectionSortValueInterface::class, $context);

        return $items->sortBy(
            static fn (mixed $value, int|string $key): mixed => $callback->sortValue($value, $key),
            $this->options,
            $this->descending,
        );
    }
}
