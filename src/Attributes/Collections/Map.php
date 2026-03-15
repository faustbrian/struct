<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\Collections;

use Attribute;
use Cline\Struct\Contracts\MapsCollectionItemsInterface;
use Cline\Struct\Contracts\TransformsLazyCollectionValueInterface;
use Cline\Struct\Support\CreationContext;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Map extends AbstractCollectionCallbackTransformer implements TransformsLazyCollectionValueInterface
{
    public function transformCollection(Collection $items, ?CreationContext $context = null): Collection
    {
        /** @var MapsCollectionItemsInterface $callback */
        $callback = $this->resolveCallback(MapsCollectionItemsInterface::class, $context);

        return $items->map(
            static fn (mixed $value, int|string $key): mixed => $callback->map($value, $key),
        );
    }

    public function transformLazyCollection(LazyCollection $items, ?CreationContext $context = null): LazyCollection
    {
        /** @var MapsCollectionItemsInterface $callback */
        $callback = $this->resolveCallback(MapsCollectionItemsInterface::class, $context);

        return $items->map(
            static fn (mixed $value, int|string $key): mixed => $callback->map($value, $key),
        );
    }
}
