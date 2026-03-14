<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\Collections;

use Attribute;
use Cline\Struct\Contracts\ComputesCollectionGroupKeyInterface;
use Cline\Struct\Support\CreationContext;
use Illuminate\Support\Collection;
use Stringable;
use UnitEnum;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class CountBy extends AbstractCollectionCallbackTransformer
{
    public function transformCollection(Collection $items, ?CreationContext $context = null): Collection
    {
        /** @var ComputesCollectionGroupKeyInterface $callback */
        $callback = $this->resolveCallback(ComputesCollectionGroupKeyInterface::class, $context);

        return $items->countBy(static function (mixed $value, int|string $key) use ($callback): int|string|UnitEnum {
            $group = $callback->groupKey($value, $key);

            if ($group instanceof UnitEnum) {
                return $group;
            }

            if ($group instanceof Stringable) {
                return (string) $group;
            }

            return $group;
        });
    }
}
