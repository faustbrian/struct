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
 * Masks a portion of a string property.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Mask extends AbstractStringTransformer
{
    public function __construct(
        public string $character,
        public int $index,
        public ?int $length = null,
        public string $encoding = 'UTF-8',
    ) {}

    public function transform(string $value): string
    {
        return Str::mask($value, $this->character, $this->index, $this->length, $this->encoding);
    }
}
