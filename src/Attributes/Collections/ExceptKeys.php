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

use function array_diff_key;
use function array_flip;

/**
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class ExceptKeys extends AbstractCollectionTransformer
{
    /**
     * @param array<int, array-key> $keys
     */
    public function __construct(
        public array $keys,
    ) {}

    #[Override()]
    public function supportsLists(): bool
    {
        return false;
    }

    public function transform(array $items): array
    {
        return array_diff_key($items, array_flip($this->keys));
    }
}
