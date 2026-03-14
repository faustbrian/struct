<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\Numerus;

use Attribute;
use Cline\Struct\Casts\NumericCast;
use Cline\Struct\Contracts\ConfiguresNumericRoundingInterface;
use Cline\Struct\Contracts\ProvidesCastClassInterface;
use RoundingMode;

/**
 * Rounds a numeric property to the nearest value with ties away from zero.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class RoundHalfUp implements ConfiguresNumericRoundingInterface, ProvidesCastClassInterface
{
    public function __construct(
        public int $precision = 0,
        public RoundingMode $mode = RoundingMode::HalfAwayFromZero,
    ) {}

    public function castClass(): string
    {
        return NumericCast::class;
    }

    public function precision(): int
    {
        return $this->precision;
    }

    public function mode(): RoundingMode
    {
        return $this->mode;
    }
}
