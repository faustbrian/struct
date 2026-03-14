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
 * Ensures a string property ends with the given suffix.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Finish extends AbstractStringTransformer
{
    public function __construct(
        public string $cap,
    ) {}

    public function transform(string $value): string
    {
        return Str::finish($value, $this->cap);
    }
}
