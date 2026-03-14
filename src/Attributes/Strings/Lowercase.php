<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\Strings;

use Attribute;
use Illuminate\Support\Str;

/**
 * Converts a string property to lowercase.
 *
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Lowercase extends AbstractStringTransformer
{
    public function transform(string $value): string
    {
        return Str::lower($value);
    }
}
