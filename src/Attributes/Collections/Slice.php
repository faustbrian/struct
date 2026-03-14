<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\Collections;

use Attribute;

use function array_slice;

/**
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Slice extends AbstractCollectionTransformer
{
    public function __construct(
        public int $offset,
        public ?int $length = null,
        public bool $preserveKeys = true,
    ) {}

    public function transform(array $items): array
    {
        return array_slice($items, $this->offset, $this->length, $this->preserveKeys);
    }
}
