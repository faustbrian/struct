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
 * Raised when input contains keys that are not accepted by a data object.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class SuperfluousInputKeyException extends AbstractStructException
{
    /**
     * Create an exception for an unexpected input key.
     *
     * @param class-string $class
     */
    public static function forKey(string $class, string $key): self
    {
        return new self(sprintf('Superfluous input key [%s] for data object [%s].', $key, $class));
    }
}
