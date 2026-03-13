<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes;

use Attribute;
use Cline\Struct\Casts\NumericCast;
use Cline\Struct\Contracts\ProvidesCastClassInterface;

/**
 * Clamps a numeric property into an inclusive range.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Clamp implements ProvidesCastClassInterface
{
    public function __construct(
        public int|float $min,
        public int|float $max,
    ) {}

    public function castClass(): string
    {
        return NumericCast::class;
    }
}
