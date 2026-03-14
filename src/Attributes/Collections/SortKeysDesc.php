<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\Collections;

use Attribute;
use Override;

use const SORT_REGULAR;

use function krsort;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class SortKeysDesc extends AbstractCollectionTransformer
{
    public function __construct(
        public int $flags = SORT_REGULAR,
    ) {}

    #[Override()]
    public function supportsLists(): bool
    {
        return false;
    }

    public function transform(array $items): array
    {
        krsort($items, $this->flags);

        return $items;
    }
}
