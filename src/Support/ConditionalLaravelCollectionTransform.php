<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Support;

use Cline\Struct\Contracts\TransformsLaravelCollectionValueInterface;
use Closure;
use Illuminate\Support\Collection;

/**
 * @author Brian Faust <brian@cline.sh>
 * @internal
 * @psalm-immutable
 */
final readonly class ConditionalLaravelCollectionTransform implements TransformsLaravelCollectionValueInterface
{
    /**
     * @param Closure(Collection<array-key, mixed>, ?CreationContext): bool $condition
     */
    public function __construct(
        private Closure $condition,
        private TransformsLaravelCollectionValueInterface $next,
    ) {}

    public function transformCollection(Collection $items, ?CreationContext $context = null): Collection
    {
        if (($this->condition)($items, $context) !== true) {
            return $items;
        }

        return $this->next->transformCollection($items, $context);
    }
}
