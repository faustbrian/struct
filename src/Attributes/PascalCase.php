<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes;

use Attribute;
use Illuminate\Support\Str;

/**
 * Converts a string property to pascal case.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class PascalCase extends AbstractStringTransformer
{
    public function transform(string $value): string
    {
        return Str::pascal($value);
    }
}
