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
 * Repeats a string property the requested number of times.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Repeat extends AbstractStringTransformer
{
    public function __construct(
        public int $times,
    ) {}

    public function transform(string $value): string
    {
        return Str::repeat($value, $this->times);
    }
}
