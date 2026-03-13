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
 * Transliterate a string property to ASCII.
 *
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Ascii extends AbstractStringTransformer
{
    public function __construct(
        public string $language = 'en',
    ) {}

    public function transform(string $value): string
    {
        return Str::ascii($value, $this->language);
    }
}
