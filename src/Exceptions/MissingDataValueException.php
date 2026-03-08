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
 * Raised when required data object constructor input is missing.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class MissingDataValueException extends AbstractStructException
{
    /**
     * Create an exception for a missing data object property value.
     *
     * @param class-string $class
     */
    public static function forProperty(string $class, string $property): self
    {
        return new self(sprintf('Missing value for property [%s] on data object [%s].', $property, $class));
    }
}
