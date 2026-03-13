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
 * Trims trailing whitespace from a string property.
 *
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class RightTrim extends AbstractStringTransformer
{
    public function __construct(
        public ?string $charlist = null,
    ) {}

    public function transform(string $value): string
    {
        return Str::rtrim($value, $this->charlist);
    }
}
