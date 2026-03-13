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
 * Keeps the content before the last delimiter in a string property.
 *
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class BeforeLast extends AbstractStringTransformer
{
    public function __construct(
        public string $search,
    ) {}

    public function transform(string $value): string
    {
        return Str::beforeLast($value, $this->search);
    }
}
