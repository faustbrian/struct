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
 * Replaces matching values in a string property.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Replace extends AbstractStringTransformer
{
    /**
     * @param array<int, string>|string $search
     * @param array<int, string>|string $replace
     */
    public function __construct(
        public string|array $search,
        public string|array $replace,
        public bool $caseSensitive = true,
    ) {}

    public function transform(string $value): string
    {
        return Str::replace($this->search, $this->replace, $value, $this->caseSensitive);
    }
}
