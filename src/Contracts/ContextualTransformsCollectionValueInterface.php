<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

use Cline\Struct\Support\PropertyHydrationContext;

/**
 * Declares a collection transform that can inspect whole-DTO hydration context.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface ContextualTransformsCollectionValueInterface extends TransformsCollectionValueInterface
{
    /**
     * @param  array<array-key, mixed> $items
     * @return array<array-key, mixed>
     */
    public function transformWithContext(array $items, PropertyHydrationContext $context): array;
}
