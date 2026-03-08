<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

use Cline\Struct\Metadata\PropertyMetadata;
use Cline\Struct\Serialization\SerializationOptions;

/**
 * Determines whether a property should be included during serialization.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface SerializationConditionInterface
{
    /**
     * Decide whether the property should be included for the current data object.
     */
    public function shouldInclude(
        DataObjectInterface $data,
        PropertyMetadata $property,
        SerializationOptions $options,
    ): bool;
}
