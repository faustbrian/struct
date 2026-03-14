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
 * Wraps a string property with the given delimiters.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Wrap extends AbstractStringTransformer
{
    public function __construct(
        public string $before,
        public ?string $after = null,
    ) {}

    public function transform(string $value): string
    {
        return Str::wrap($value, $this->before, $this->after);
    }
}
