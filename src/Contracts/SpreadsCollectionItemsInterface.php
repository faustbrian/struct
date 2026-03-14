<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

/**
 * Declares a spread callback for nested collection items.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface SpreadsCollectionItemsInterface
{
    public function spread(mixed ...$values): mixed;
}
