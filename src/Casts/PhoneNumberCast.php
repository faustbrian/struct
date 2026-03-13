<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Casts;

use Cline\PhoneNumber\PhoneNumber;
use Cline\Struct\Attributes\AsPhoneNumber;
use Cline\Struct\Contracts\CastInterface;
use Cline\Struct\Contracts\ProvidesCastClassInterface;
use Cline\Struct\Metadata\PropertyMetadata;
use InvalidArgumentException;
use ReflectionAttribute;

use function array_merge;
use function is_array;
use function is_string;
use function throw_unless;

/**
 * Casts values to and from `PhoneNumber` instances.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class PhoneNumberCast implements CastInterface
{
    public function get(PropertyMetadata $property, mixed $value): mixed
    {
        if ($value === null || $value instanceof PhoneNumber) {
            return $value;
        }

        if (is_array($value)) {
            /** @var array<string, mixed> $value */
            return $this->fromArray($value);
        }

        throw_unless(is_string($value), InvalidArgumentException::class, 'PhoneNumberCast only supports PhoneNumber, array, or string values.');

        $configuration = $this->configuration($property);

        return PhoneNumber::parse($value, $configuration?->regionCode);
    }

    public function set(PropertyMetadata $property, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $phoneNumber = $this->get($property, $value);

        return $phoneNumber instanceof PhoneNumber ? $phoneNumber->jsonSerialize() : $value;
    }

    /**
     * @param array<string, mixed> $value
     */
    private function fromArray(array $value): PhoneNumber
    {
        $phoneNumber = $value['phoneNumber'] ?? $value['number'] ?? $value['value'] ?? null;
        $regionCode = $value['regionCode'] ?? $value['region'] ?? null;

        throw_unless(is_string($phoneNumber), InvalidArgumentException::class, 'Phone number arrays must include a string phone number.');
        throw_unless($regionCode === null || is_string($regionCode), InvalidArgumentException::class, 'Phone number regions must be strings.');

        return PhoneNumber::parse($phoneNumber, $regionCode);
    }

    private function configuration(PropertyMetadata $property): ?AsPhoneNumber
    {
        foreach ($this->attributes($property) as $attribute) {
            if ($attribute instanceof AsPhoneNumber) {
                return $attribute;
            }
        }

        return null;
    }

    /**
     * @return list<object>
     */
    private function attributes(PropertyMetadata $property): array
    {
        $propertyAttributes = $property->property?->getAttributes() ?? [];
        $parameterAttributes = $propertyAttributes === [] ? $property->parameter->getAttributes() : [];

        return $this->instantiateAttributes(array_merge($propertyAttributes, $parameterAttributes));
    }

    /**
     * @param  list<ReflectionAttribute<object>> $attributes
     * @return list<object>
     */
    private function instantiateAttributes(array $attributes): array
    {
        $instances = [];

        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();

            if (!$instance instanceof ProvidesCastClassInterface) {
                continue;
            }

            if ($instance->castClass() !== self::class) {
                continue;
            }

            $instances[] = $instance;
        }

        return $instances;
    }
}
