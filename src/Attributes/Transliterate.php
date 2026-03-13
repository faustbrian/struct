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
 * Transliterate a string property with configurable unknown-character handling.
 *
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Transliterate extends AbstractStringTransformer
{
    public function __construct(
        public string $unknown = '?',
        public bool $strict = false,
    ) {}

    public function transform(string $value): string
    {
        return Str::transliterate($value, $this->unknown, $this->strict);
    }
}
