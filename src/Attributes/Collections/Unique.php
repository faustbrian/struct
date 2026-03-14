<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\Collections;

use Attribute;

use function in_array;

/**
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Unique extends AbstractCollectionTransformer
{
    public function __construct(
        public bool $strict = false,
    ) {}

    public function transform(array $items): array
    {
        $unique = [];

        foreach ($items as $key => $item) {
            if (in_array($item, $unique, $this->strict)) {
                continue;
            }

            $unique[$key] = $item;
        }

        return $unique;
    }
}
