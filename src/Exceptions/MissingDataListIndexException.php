<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Exceptions;

use function sprintf;

/**
 * Raised when a requested index does not exist in the data list.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class MissingDataListIndexException extends AbstractDataListException
{
    public static function forIndex(int $index): self
    {
        return new self(sprintf('Index %d is not present in the data list.', $index));
    }
}
