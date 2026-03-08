<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

use Cline\Struct\Metadata\PropertyMetadata;

/**
 * Converts property values to and from their serialized representation.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface CastInterface
{
    /**
     * Cast a model-serialized value into a property value.
     */
    public function get(PropertyMetadata $property, mixed $value): mixed;

    /**
     * Cast a property value into a model-storable value.
     */
    public function set(PropertyMetadata $property, mixed $value): mixed;
}
