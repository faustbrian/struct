<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Casts;

use Cline\Math\BigDecimal;
use Cline\Math\BigInteger;
use Cline\Math\BigNumber;
use Cline\Math\BigRational;
use Cline\Struct\Contracts\CastInterface;
use Cline\Struct\Metadata\PropertyMetadata;
use InvalidArgumentException;

use function is_finite;
use function is_float;
use function is_int;
use function is_string;
use function throw_unless;

/**
 * Casts values to and from `BigNumber` instances.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class BigNumberCast implements CastInterface
{
    public function get(PropertyMetadata $property, mixed $value): mixed
    {
        if ($value === null && $property->nullable) {
            return null;
        }

        if ($value instanceof BigNumber) {
            return $this->targetClass($property)::of($value);
        }

        return $this->targetClass($property)::of($this->normalizeValue($value));
    }

    public function set(PropertyMetadata $property, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $number = $this->get($property, $value);

        return $number instanceof BigNumber ? $number->jsonSerialize() : $value;
    }

    /**
     * @return class-string<BigDecimal|BigInteger|BigNumber|BigRational>
     */
    private function targetClass(PropertyMetadata $property): string
    {
        foreach ($property->types as $type) {
            if ($type === BigInteger::class) {
                return BigInteger::class;
            }

            if ($type === BigDecimal::class) {
                return BigDecimal::class;
            }

            if ($type === BigRational::class) {
                return BigRational::class;
            }

            if ($type === BigNumber::class) {
                return BigNumber::class;
            }
        }

        return BigNumber::class;
    }

    private function normalizeValue(mixed $value): int|string
    {
        if (is_int($value) || is_string($value)) {
            return $value;
        }

        throw_unless(is_float($value) && is_finite($value), InvalidArgumentException::class, 'BigNumberCast only supports int, float, string, or BigNumber values.');

        return (string) $value;
    }
}
