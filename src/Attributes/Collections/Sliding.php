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

use function max;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Sliding implements TransformsLaravelCollectionValueInterface
{
    public function __construct(
        public int $size,
        public int $step = 1,
    ) {}

    public function transformCollection(Collection $items, ?CreationContext $context = null): Collection
    {
        /** @var int<1, max> $size */
        $size = max($this->size, 1);

        /** @var int<1, max> $step */
        $step = max($this->step, 1);

        return $items->sliding($size, $step);
    }
}
