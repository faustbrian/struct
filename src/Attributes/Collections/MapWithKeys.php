<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\Collections;

use Attribute;
use Cline\Struct\Contracts\MapsCollectionItemsWithKeysInterface;
use Cline\Struct\Support\CreationContext;
use Illuminate\Support\Collection;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class MapWithKeys extends AbstractCollectionCallbackTransformer
{
    public function transformCollection(Collection $items, ?CreationContext $context = null): Collection
    {
        /** @var MapsCollectionItemsWithKeysInterface $callback */
        $callback = $this->resolveCallback(MapsCollectionItemsWithKeysInterface::class, $context);

        return $items->mapWithKeys(
            static fn (mixed $value, int|string $key): array => $callback->mapWithKeys($value, $key),
        );
    }
}
