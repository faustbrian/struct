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

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class GroupBy extends AbstractCollectionCallbackTransformer
{
    public function __construct(
        string $callback,
        public bool $preserveKeys = false,
    ) {
        parent::__construct($callback);
    }

    public function transformCollection(Collection $items, ?CreationContext $context = null): Collection
    {
        /** @var ComputesCollectionGroupKeyInterface $callback */
        $callback = $this->resolveCallback(ComputesCollectionGroupKeyInterface::class, $context);

        return $items->groupBy(
            static fn (mixed $value, int|string $key): mixed => $callback->groupKey($value, $key),
            $this->preserveKeys,
        );
    }
}
