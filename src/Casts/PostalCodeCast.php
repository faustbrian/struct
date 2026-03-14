<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Casts;

use Cline\PostalCode\PostalCode;
use Cline\PostalCode\PostalCodeManager;
use Cline\Struct\Attributes\PostalCode\AsPostalCode;
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
 * Casts values to and from `PostalCode` instances.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class PostalCodeCast implements CastInterface
{
    public function get(PropertyMetadata $property, mixed $value): mixed
    {
        if ($value === null || $value instanceof PostalCode) {
            return $value;
        }

        if (is_array($value)) {
            /** @var array<string, mixed> $value */
            return $this->fromArray($value);
        }

        $configuration = $this->configuration($property);

        throw_unless(is_string($value), InvalidArgumentException::class, 'PostalCodeCast only supports PostalCode, array, or string values.');
        throw_unless($configuration instanceof AsPostalCode && is_string($configuration->country), InvalidArgumentException::class, 'PostalCodeCast requires a country when hydrating scalar postal code values.');

        return $this->manager()->for($value, $configuration->country);
    }

    public function set(PropertyMetadata $property, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $postalCode = $this->get($property, $value);

        return $postalCode instanceof PostalCode
            ? [
                'postalCode' => (string) $postalCode,
                'country' => $postalCode->country(),
            ]
            : $value;
    }

    /**
     * @param array<string, mixed> $value
     */
    private function fromArray(array $value): PostalCode
    {
        $postalCode = $value['postalCode'] ?? $value['code'] ?? $value['value'] ?? null;
        $country = $value['country'] ?? null;

        throw_unless(is_string($postalCode), InvalidArgumentException::class, 'Postal code arrays must include a string postal code.');
        throw_unless(is_string($country), InvalidArgumentException::class, 'Postal code arrays must include a string country code.');

        return $this->manager()->for($postalCode, $country);
    }

    private function configuration(PropertyMetadata $property): ?AsPostalCode
    {
        foreach ($this->attributes($property) as $attribute) {
            if ($attribute instanceof AsPostalCode) {
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

    private function manager(): PostalCodeManager
    {
        return new PostalCodeManager();
    }
}
