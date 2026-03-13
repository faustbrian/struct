<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Casts;

use Cline\Numerus\Numerus;
use Cline\Struct\Contracts\CastInterface;
use Cline\Struct\Metadata\PropertyMetadata;
use InvalidArgumentException;

use function is_float;
use function is_int;
use function is_numeric;
use function is_string;
use function str_contains;
use function throw_unless;

/**
 * Casts values to and from `Numerus` instances.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class NumerusCast implements CastInterface
{
    public function get(PropertyMetadata $property, mixed $value): mixed
    {
        if ($value === null || $value instanceof Numerus) {
            return $value;
        }

        return Numerus::create($this->normalizeValue($value));
    }

    public function set(PropertyMetadata $property, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof Numerus
            ? $value->value()
            : Numerus::create($this->normalizeValue($value))->value();
    }

    private function normalizeValue(mixed $value): int|float|string
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }

        throw_unless(is_string($value), InvalidArgumentException::class, 'NumerusCast only supports int, float, string, or Numerus values.');

        if (!is_numeric($value)) {
            return $value;
        }

        return str_contains($value, '.') ? (float) $value : (int) $value;
    }
}
