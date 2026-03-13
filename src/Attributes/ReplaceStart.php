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
 * Replaces a matching prefix in a string property.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class ReplaceStart extends AbstractStringTransformer
{
    public function __construct(
        public string $search,
        public string $replace,
    ) {}

    public function transform(string $value): string
    {
        return Str::replaceStart($this->search, $this->replace, $value);
    }
}
