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
 * Limits the number of characters in a string property.
 *
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Limit extends AbstractStringTransformer
{
    public function __construct(
        public int $limit = 100,
        public string $end = '...',
        public bool $preserveWords = false,
    ) {}

    public function transform(string $value): string
    {
        return Str::limit($value, $this->limit, $this->end, $this->preserveWords);
    }
}
