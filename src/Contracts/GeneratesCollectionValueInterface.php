<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

use Cline\Struct\Support\CreationContext;
use Illuminate\Support\Collection;

/**
 * Declares an attribute-backed generated collection property.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface GeneratesCollectionValueInterface
{
    /**
     * @param  array<string, mixed>         $properties
     * @return Collection<array-key, mixed>
     */
    public function generateCollection(array $properties, ?CreationContext $context = null): Collection;
}
