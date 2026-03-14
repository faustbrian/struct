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
use Cline\Struct\Support\CreationContext;
use Illuminate\Support\Collection;

use function is_array;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class FlatMap extends AbstractCollectionCallbackTransformer
{
    public function transformCollection(Collection $items, ?CreationContext $context = null): Collection
    {
        /** @var MapsCollectionItemsInterface $callback */
        $callback = $this->resolveCallback(MapsCollectionItemsInterface::class, $context);

        return $items->flatMap(
            static function (mixed $value, int|string $key) use ($callback): array|Collection {
                $mapped = $callback->map($value, $key);

                if ($mapped instanceof Collection || is_array($mapped)) {
                    return $mapped;
                }

                return [$mapped];
            },
        );
    }
}
