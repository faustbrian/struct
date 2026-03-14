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

/**
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class RejectFalsy extends AbstractCollectionTransformer
{
    public function transform(array $items): array
    {
        return array_filter($items, static fn (mixed $item): bool => (bool) $item);
    }
}
