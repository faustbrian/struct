<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\Collections;

use Attribute;

use function array_filter;
use function is_string;
use function mb_trim;

/**
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class RejectEmptyStrings extends AbstractCollectionTransformer
{
    public function transform(array $items): array
    {
        return array_filter($items, static fn (mixed $item): bool => !is_string($item) || mb_trim($item) !== '');
    }
}
