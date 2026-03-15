<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Casts;

use Cline\Struct\Contracts\CastInterface;
use Cline\Struct\Contracts\ContextualCastInterface;
use Cline\Struct\Contracts\ContextualTransformsStringValueInterface;
use Cline\Struct\Contracts\ProvidesCastClassInterface;
use Cline\Struct\Contracts\TransformsStringValueInterface;
use Cline\Struct\Metadata\PropertyMetadata;
use Cline\Struct\Support\PropertyHydrationContext;
use ReflectionAttribute;

use function array_merge;
use function is_string;

/**
 * Applies string normalization attributes through deterministic transforms.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class StringCast implements CastInterface, ContextualCastInterface
{
    public function get(PropertyMetadata $property, mixed $value): mixed
    {
        return $this->transform($property, $value);
    }

    public function getWithContext(
        PropertyMetadata $property,
        mixed $value,
        PropertyHydrationContext $context,
    ): mixed {
        return $this->transform($property, $value, $context);
    }

    public function set(PropertyMetadata $property, mixed $value): mixed
    {
        return $value;
    }

    private function transform(
        PropertyMetadata $property,
        mixed $value,
        ?PropertyHydrationContext $context = null,
    ): mixed {
        if (!is_string($value)) {
            return $value;
        }

        foreach ($this->attributes($property) as $attribute) {
            if ($attribute instanceof ContextualTransformsStringValueInterface && $context instanceof PropertyHydrationContext) {
                $value = $attribute->transformWithContext($value, $context);

                continue;
            }

            $value = $attribute->transform($value);
        }

        return $value;
    }

    /**
     * @return list<TransformsStringValueInterface>
     */
    private function attributes(PropertyMetadata $property): array
    {
        $propertyAttributes = $property->property?->getAttributes() ?? [];
        $parameterAttributes = $propertyAttributes === [] ? $property->parameter->getAttributes() : [];

        return $this->instantiateAttributes(array_merge($propertyAttributes, $parameterAttributes));
    }

    /**
     * @param  list<ReflectionAttribute<object>>    $attributes
     * @return list<TransformsStringValueInterface>
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

            if (!$instance instanceof TransformsStringValueInterface) {
                continue;
            }

            $instances[] = $instance;
        }

        return $instances;
    }
}
