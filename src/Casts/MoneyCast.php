<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Casts;

use Cline\Money\Context;
use Cline\Money\Context\AutoContext;
use Cline\Money\Context\CashContext;
use Cline\Money\Context\CustomContext;
use Cline\Money\Context\DefaultContext;
use Cline\Money\Money;
use Cline\Struct\Attributes\AsMoney;
use Cline\Struct\Contracts\CastInterface;
use Cline\Struct\Contracts\ProvidesCastClassInterface;
use Cline\Struct\Metadata\PropertyMetadata;
use InvalidArgumentException;
use ReflectionAttribute;

use function array_merge;
use function count;
use function is_array;
use function is_float;
use function is_int;
use function is_numeric;
use function is_string;
use function preg_split;
use function str_contains;
use function throw_unless;
use function trim;

/**
 * Casts values to and from `Money` instances.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class MoneyCast implements CastInterface
{
    public function get(PropertyMetadata $property, mixed $value): mixed
    {
        if ($value === null || $value instanceof Money) {
            return $value;
        }

        if (is_array($value)) {
            /** @var array<string, mixed> $value */
            return $this->fromArray($value);
        }

        if (is_string($value) && !is_numeric($value)) {
            return $this->fromString($value, $property);
        }

        $configuration = $this->configuration($property);

        throw_unless($configuration instanceof AsMoney && is_string($configuration->currency), InvalidArgumentException::class, 'MoneyCast requires a currency when hydrating scalar money values.');
        throw_unless(is_int($value) || is_float($value) || is_string($value), InvalidArgumentException::class, 'MoneyCast only supports Money, array, int, float, or string values.');

        return $configuration->minor
            ? Money::ofMinor($this->normalizeNumber($value), $configuration->currency)
            : Money::of($this->normalizeNumber($value), $configuration->currency);
    }

    public function set(PropertyMetadata $property, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $money = $this->get($property, $value);

        return $money instanceof Money ? $money->jsonSerialize() : $value;
    }

    /**
     * @param array<string, mixed> $value
     */
    private function fromArray(array $value): Money
    {
        $amount = $value['amount'] ?? null;
        $currency = $value['currency'] ?? null;

        throw_unless(is_string($currency) || is_int($currency), InvalidArgumentException::class, 'Money arrays must include a currency code.');
        throw_unless(is_string($amount) || is_int($amount) || is_float($amount), InvalidArgumentException::class, 'Money arrays must include a numeric amount.');

        $context = $this->contextFromArray($value['context'] ?? null);

        return Money::of($this->normalizeNumber($amount), $currency, $context);
    }

    private function fromString(string $value, PropertyMetadata $property): Money
    {
        $parts = preg_split('/\s+/', trim($value), 2);

        if (is_array($parts) && count($parts) === 2 && $parts[0] !== '') {
            return Money::of($this->normalizeNumber($parts[1]), $parts[0]);
        }

        $configuration = $this->configuration($property);

        throw_unless($configuration instanceof AsMoney && is_string($configuration->currency), InvalidArgumentException::class, 'MoneyCast requires a currency when hydrating scalar money values.');

        return $configuration->minor
            ? Money::ofMinor($this->normalizeNumber($value), $configuration->currency)
            : Money::of($this->normalizeNumber($value), $configuration->currency);
    }

    /**
     * @param array<string, mixed>|mixed $value
     */
    private function contextFromArray(mixed $value): ?Context
    {
        if (!is_array($value)) {
            return null;
        }

        $type = $value['type'] ?? null;

        return match ($type) {
            'default' => new DefaultContext(),
            'cash' => new CashContext($this->intValue($value['step'] ?? null, 1)),
            'custom' => new CustomContext(
                $this->intValue($value['scale'] ?? null, 0),
                $this->intValue($value['step'] ?? null, 1),
            ),
            'auto' => new AutoContext(),
            default => null,
        };
    }

    private function configuration(PropertyMetadata $property): ?AsMoney
    {
        foreach ($this->attributes($property) as $attribute) {
            if ($attribute instanceof AsMoney) {
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

    private function normalizeNumber(int|float|string $value): int|float|string
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }

        if (!is_numeric($value)) {
            return $value;
        }

        return str_contains($value, '.') ? (float) $value : (int) $value;
    }

    private function intValue(mixed $value, int $default): int
    {
        return is_int($value) ? $value : $default;
    }
}
