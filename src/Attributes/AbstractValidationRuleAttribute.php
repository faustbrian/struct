<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes;

use BackedEnum;
use Cline\Struct\Contracts\ProvidesValidationRulesInterface;
use UnitEnum;

use function array_map;
use function array_values;
use function implode;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;

/**
 * Shared base helpers for validation rule attribute DTOs.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
abstract readonly class AbstractValidationRuleAttribute implements ProvidesValidationRulesInterface
{
    /**
     * Convert an individual validation parameter into a string representation.
     */
    protected static function stringify(mixed $value): string
    {
        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        if ($value instanceof UnitEnum) {
            return $value->name;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            return static::stringifyList(array_values($value));
        }

        if (is_int($value) || is_float($value) || is_string($value)) {
            return (string) $value;
        }

        return '';
    }

    /**
     * Convert a list of values into a comma-separated validation parameter list.
     *
     * @param array<int, mixed> $values
     */
    protected static function stringifyList(array $values): string
    {
        return implode(',', array_map(static::stringify(...), array_values($values)));
    }

    /**
     * Build a Laravel rule entry from a rule name and optional parameters.
     *
     * @return array<int, string>
     */
    protected static function rule(string $name, mixed ...$parameters): array
    {
        if ($parameters === []) {
            return [$name];
        }

        return [$name.':'.static::stringifyList(array_values($parameters))];
    }
}
