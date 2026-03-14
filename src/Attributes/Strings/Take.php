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
 * Takes the requested number of characters from the start or end of a string property.
 *
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Take extends AbstractStringTransformer
{
    public function __construct(
        public int $limit,
    ) {}

    public function transform(string $value): string
    {
        return Str::take($value, $this->limit);
    }
}
