<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\Collections;

use Attribute;

use const SORT_REGULAR;

use function arsort;
use function asort;

/**
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class SortValues extends AbstractCollectionTransformer
{
    public function __construct(
        public bool $descending = false,
        public int $flags = SORT_REGULAR,
    ) {}

    public function transform(array $items): array
    {
        if ($this->descending) {
            arsort($items, $this->flags);

            return $items;
        }

        asort($items, $this->flags);

        return $items;
    }
}
