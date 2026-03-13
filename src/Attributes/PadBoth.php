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
 * Pads both sides of a string property to the requested length.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class PadBoth extends AbstractStringTransformer
{
    public function __construct(
        public int $length,
        public string $pad = ' ',
    ) {}

    public function transform(string $value): string
    {
        return Str::padBoth($value, $this->length, $this->pad);
    }
}
