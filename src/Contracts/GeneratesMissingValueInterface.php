<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

/**
 * Supplies a property value when the input key is missing during hydration.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface GeneratesMissingValueInterface
{
    public function generate(): mixed;
}
