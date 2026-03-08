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
 * Resolves the value for a lazy property at serialization time.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface ResolvesLazyValueInterface
{
    /**
     * Resolve the property's value for the given data object and options.
     */
    public function resolve(
        DataObjectInterface $data,
        PropertyMetadata $property,
        SerializationOptions $options,
    ): mixed;
}
