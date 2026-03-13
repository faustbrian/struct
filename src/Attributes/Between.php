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
 * Keeps the content between the first matching delimiters in a string property.
 *
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Between extends AbstractStringTransformer
{
    public function __construct(
        public string $from,
        public string $to,
    ) {}

    public function transform(string $value): string
    {
        return Str::between($value, $this->from, $this->to);
    }
}
