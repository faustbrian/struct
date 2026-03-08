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
 * Raised when a data object requests a factory that is not configured.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class UnsupportedFactoryException extends AbstractStructException
{
    /**
     * Create an exception for a data object without a configured factory attribute.
     *
     * @param class-string $class
     */
    public static function forDataObject(string $class): self
    {
        return new self(sprintf('Data object [%s] does not declare a #[UseFactory] attribute.', $class));
    }
}
