<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Casts;

use Cline\Numerus\Numerus;
use Cline\Struct\Attributes\Numerus\Abs;
use Cline\Struct\Attributes\Numerus\Clamp;
use Cline\Struct\Contracts\CastInterface;
use Cline\Struct\Contracts\ConfiguresNumericRoundingInterface;
use Cline\Struct\Contracts\ProvidesCastClassInterface;
use Cline\Struct\Metadata\PropertyMetadata;
use InvalidArgumentException;
use ReflectionAttribute;

use function array_merge;
use function is_float;
use function is_int;
use function is_numeric;
use function is_string;
use function str_contains;
use function throw_unless;

/**
 * Applies numeric normalization attributes through Numerus.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class NumericCast implements CastInterface
{
    public function get(PropertyMetadata $property, mixed $value): mixed
    {
        return $this->transform($property, $value);
    }

    public function set(PropertyMetadata $property, mixed $value): mixed
    {
        return $this->transform($property, $value);
    }

    private function transform(PropertyMetadata $property, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $numerus = $value instanceof Numerus ? $value : Numerus::create($this->normalizeValue($value));

        foreach ($this->attributes($property) as $attribute) {
            if ($attribute instanceof ConfiguresNumericRoundingInterface) {
                $numerus = $numerus->round($attribute->precision(), $attribute->mode());

                continue;
            }

            if ($attribute instanceof Clamp) {
                $numerus = $numerus->clamp($attribute->min, $attribute->max);

                continue;
            }

            if (!$attribute instanceof Abs) {
                continue;
            }

            $numerus = $numerus->abs();
        }

        return $this->castForProperty($property, $numerus, $value);
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

    private function castForProperty(PropertyMetadata $property, Numerus $numerus, mixed $original): mixed
    {
        foreach ($property->types as $type) {
            if ($type === 'null') {
                continue;
            }

            return match ($type) {
                Numerus::class => $numerus,
                'int' => $numerus->toInt(),
                'float' => $numerus->toFloat(),
                'string' => $numerus->toString(),
                default => $this->castLikeOriginal($numerus, $original),
            };
        }

        return $this->castLikeOriginal($numerus, $original);
    }

    private function castLikeOriginal(Numerus $numerus, mixed $original): mixed
    {
        return match (true) {
            $original instanceof Numerus => $numerus,
            is_int($original) => $numerus->toInt(),
            is_float($original) => $numerus->toFloat(),
            is_string($original) => $numerus->toString(),
            default => $numerus->value(),
        };
    }

    private function normalizeValue(mixed $value): int|float|string
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }

        throw_unless(is_string($value), InvalidArgumentException::class, 'NumericCast only supports int, float, string, or Numerus values.');

        if (!is_numeric($value)) {
            return $value;
        }

        return str_contains($value, '.') ? (float) $value : (int) $value;
    }
}
