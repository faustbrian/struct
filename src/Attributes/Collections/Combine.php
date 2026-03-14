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
final readonly class Combine implements TransformsLaravelCollectionValueInterface
{
    /**
     * @param array<array-key, mixed> $values
     */
    public function __construct(
        public array $values,
    ) {}

    public function transformCollection(Collection $items, ?CreationContext $context = null): Collection
    {
        return $items->combine($this->values);
    }
}
