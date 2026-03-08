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
 * Raised when recursive references are detected during hydration.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class RecursiveHydrationException extends AbstractStructException
{
    /**
     * Create an exception for the given hydration context path.
     */
    public static function detected(string $context): self
    {
        return new self(sprintf('Recursive hydration detected for [%s].', $context));
    }
}
