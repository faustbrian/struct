<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\Collections;

use Cline\Struct\Contracts\TransformsLaravelCollectionValueInterface;
use Cline\Struct\Contracts\WrapsLaravelCollectionTransformInterface;
use Cline\Struct\Support\ConditionalLaravelCollectionTransform;
use Cline\Struct\Support\CreationContext;
use Illuminate\Support\Collection;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
abstract readonly class AbstractConditionalCollectionTransformer implements WrapsLaravelCollectionTransformInterface
{
    public function wrap(TransformsLaravelCollectionValueInterface $next): TransformsLaravelCollectionValueInterface
    {
        return new ConditionalLaravelCollectionTransform(
            fn (Collection $items, ?CreationContext $context): bool => $this->shouldApply($items, $context),
            $next,
        );
    }

    abstract protected function shouldApply(Collection $items, ?CreationContext $context = null): bool;
}
