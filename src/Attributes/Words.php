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
 * Limits a string property to a number of words.
 *
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Words extends AbstractStringTransformer
{
    public function __construct(
        public int $words = 100,
        public string $end = '...',
    ) {}

    public function transform(string $value): string
    {
        return Str::words($value, $this->words, $this->end);
    }
}
