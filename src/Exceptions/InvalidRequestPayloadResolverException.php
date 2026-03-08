<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Exceptions;

/**
 * Raised when a resolved request payload resolver does not implement the required contract.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class InvalidRequestPayloadResolverException extends AbstractPayloadResolverException
{
    public static function forResolvedValue(): self
    {
        return new self('Resolved request payload resolver must implement RequestPayloadResolverInterface.');
    }
}
