<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

use Cline\Struct\Serialization\SerializationOptions;

/**
 * Converts a data object into a string representation.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface StringifierInterface
{
    /**
     * Stringify the given data object using the provided serialization options.
     */
    public function stringify(
        DataObjectInterface $dto,
        ?SerializationOptions $options = null,
    ): string;
}
