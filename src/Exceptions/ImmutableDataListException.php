<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Exceptions;

/**
 * Raised when mutation is attempted on an immutable data list.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class ImmutableDataListException extends AbstractDataListException
{
    public static function mutationAttempted(): self
    {
        return new self('DataList is immutable.');
    }
}
