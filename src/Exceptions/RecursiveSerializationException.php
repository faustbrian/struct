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
 * Raised when recursive object graphs are detected during serialization.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class RecursiveSerializationException extends AbstractStructException
{
    /**
     * Create an exception for the given serialization context path.
     */
    public static function detected(string $context): self
    {
        return new self(sprintf('Recursive serialization detected for [%s].', $context));
    }
}
