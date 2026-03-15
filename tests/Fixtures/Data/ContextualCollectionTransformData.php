<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Tests\Fixtures\Attributes\PrefixCollectionBySibling;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class ContextualCollectionTransformData extends AbstractData
{
    /**
     * @param array<int, string> $items
     */
    public function __construct(
        public string $prefix,
        #[PrefixCollectionBySibling()]
        public array $items,
    ) {}
}
