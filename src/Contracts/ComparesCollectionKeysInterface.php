<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

/**
 * Compares two collection keys for sorting.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface ComparesCollectionKeysInterface
{
    public function compare(int|string $left, int|string $right): int;
}
