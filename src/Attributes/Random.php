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
 * Generates a random string when the input key is missing.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Random extends AbstractGeneratedValueAttribute
{
    public function __construct(
        public int $length = 16,
        bool $lowerCase = false,
    ) {
        parent::__construct($lowerCase);
    }

    public function generate(): string
    {
        return $this->normalize(Str::random($this->length));
    }
}
