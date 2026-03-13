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
 * Raised when a generated-value attribute is configured on an unsupported property.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class InvalidGeneratedValueAttributeException extends AbstractStructInvalidArgumentException
{
    public static function forMultipleAttributes(string $data, string $property): self
    {
        return new self(sprintf('Property [%s::%s] cannot define more than one generated-value attribute.', $data, $property));
    }

    public static function forOptionalProperty(string $data, string $property): self
    {
        return new self(sprintf('Property [%s::%s] cannot combine generated-value attributes with Optional types.', $data, $property));
    }

    public static function forUnsupportedPropertyType(string $data, string $property): self
    {
        return new self(sprintf('Property [%s::%s] can only use generated-value attributes on string or ?string properties.', $data, $property));
    }

    public static function forUnsupportedUuidVersion(int $version): self
    {
        return new self(sprintf('The #[Uuid] attribute only supports UUID versions 1 through 7; received [%d].', $version));
    }

    public static function forMissingUuidArgument(int $version, string $argument): self
    {
        return new self(sprintf('The #[Uuid(version: %d)] attribute requires the [%s] argument.', $version, $argument));
    }

    public static function forMissingDependency(string $attribute, string $package): self
    {
        return new self(sprintf('The #[%s] attribute requires the [%s] package to be installed.', $attribute, $package));
    }
}
