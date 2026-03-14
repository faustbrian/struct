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
 * Raised when a collection attribute is configured on an unsupported property.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class InvalidCollectionAttributeException extends AbstractStructInvalidArgumentException
{
    public static function forUnsupportedPropertyType(string $data, string $property): self
    {
        return new self(sprintf(
            'Property [%s::%s] can only use collection attributes on array, DataList, or DataCollection properties.',
            $data,
            $property,
        ));
    }

    public static function forUnsupportedListAttribute(string $data, string $property, string $attribute): self
    {
        return new self(sprintf(
            'Property [%s::%s] cannot use [%s] on DataList because list keys are always reindexed.',
            $data,
            $property,
            $attribute,
        ));
    }
}
