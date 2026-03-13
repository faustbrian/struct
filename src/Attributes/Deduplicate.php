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
 * Collapses repeated characters in a string property.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Deduplicate extends AbstractStringTransformer
{
    /**
     * @param array<int, string>|string $characters
     */
    public function __construct(
        public string|array $characters = ' ',
    ) {}

    public function transform(string $value): string
    {
        return Str::deduplicate($value, $this->characters);
    }
}
