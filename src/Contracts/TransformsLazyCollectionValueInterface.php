<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

use Cline\Struct\Support\CreationContext;
use Illuminate\Support\LazyCollection;

/**
 * Declares an attribute-backed transform for Laravel lazy collections.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface TransformsLazyCollectionValueInterface
{
    /**
     * @param  LazyCollection<array-key, mixed> $items
     * @return LazyCollection<array-key, mixed>
     */
    public function transformLazyCollection(LazyCollection $items, ?CreationContext $context = null): LazyCollection;
}
