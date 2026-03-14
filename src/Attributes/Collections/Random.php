<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\Collections;

use Attribute;
use Cline\Struct\Contracts\TransformsLaravelCollectionValueInterface;
use Cline\Struct\Support\CreationContext;
use Illuminate\Support\Collection;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Random implements TransformsLaravelCollectionValueInterface
{
    public function __construct(
        public int $count,
        public bool $preserveKeys = false,
    ) {}

    public function transformCollection(Collection $items, ?CreationContext $context = null): Collection
    {
        return $items->random($this->count, $this->preserveKeys);
    }
}
