<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Enums;

use Illuminate\Support\Str;

/**
 * Enumerates the naming strategies available for input and output mapping.
 *
 * @author Brian Faust <brian@cline.sh>
 */
enum NameMapper: string
{
    case None = 'none';
    case SnakeCase = 'snake_case';
    case CamelCase = 'camelCase';
    case PascalCase = 'PascalCase';

    /**
     * Transform the given property name according to the selected strategy.
     */
    public function map(string $value): string
    {
        return match ($this) {
            self::None => $value,
            self::SnakeCase => Str::snake($value),
            self::CamelCase => Str::camel($value),
            self::PascalCase => Str::studly($value),
        };
    }
}
