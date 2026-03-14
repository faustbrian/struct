<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

/**
 * Declares a comparator for Laravel collection values.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface ComparesCollectionValuesInterface
{
    public function compare(mixed $left, mixed $right): int;
}
