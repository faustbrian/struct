<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

/**
 * Declares an attribute-backed collection transform for arrays and Struct collections.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface TransformsCollectionValueInterface
{
    /**
     * @param  array<array-key, mixed> $items
     * @return array<array-key, mixed>
     */
    public function transform(array $items): array;
}
