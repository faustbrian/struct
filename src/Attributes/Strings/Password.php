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
 * Generates a secure password when the input key is missing.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Password extends AbstractGeneratedValueAttribute
{
    public function __construct(
        public int $length = 32,
        public bool $letters = true,
        public bool $numbers = true,
        public bool $symbols = true,
        public bool $spaces = false,
        bool $lowerCase = false,
    ) {
        parent::__construct($lowerCase);
    }

    public function generate(): string
    {
        return $this->normalize(Str::password(
            $this->length,
            $this->letters,
            $this->numbers,
            $this->symbols,
            $this->spaces,
        ));
    }
}
