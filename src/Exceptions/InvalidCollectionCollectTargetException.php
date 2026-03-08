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
 * Raised when collection input is collected into an unsupported target.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class InvalidCollectionCollectTargetException extends AbstractCollectTargetException
{
    public static function fromTarget(string $target): self
    {
        return new self(sprintf('Unsupported collect target [%s] for collection input.', $target));
    }
}
