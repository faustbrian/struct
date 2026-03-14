<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

/**
 * Executes a side effect for a collection item.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface PerformsCollectionActionInterface
{
    public function execute(mixed $value, int|string $key): void;
}
