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
 * Ensures a string property starts with the given prefix.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Start extends AbstractStringTransformer
{
    public function __construct(
        public string $prefix,
    ) {}

    public function transform(string $value): string
    {
        return Str::start($value, $this->prefix);
    }
}
