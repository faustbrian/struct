<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\Abs;
use Cline\Struct\Attributes\Ceil;
use Cline\Struct\Attributes\Clamp;
use Cline\Struct\Attributes\Floor;
use Cline\Struct\Attributes\Round;
use Cline\Struct\Attributes\RoundCeiling;
use Cline\Struct\Attributes\RoundDown;
use Cline\Struct\Attributes\RoundFloor;
use Cline\Struct\Attributes\RoundHalfDown;
use Cline\Struct\Attributes\RoundHalfEven;
use Cline\Struct\Attributes\RoundHalfUp;
use Cline\Struct\Attributes\RoundUp;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class NumericAttributeData extends AbstractData
{
    public function __construct(
        #[Round(precision: 2)]
        public float $rounded,
        #[RoundUp(precision: 2)]
        public float $roundedUp,
        #[RoundDown(precision: 2)]
        public float $roundedDown,
        #[RoundHalfUp(precision: 2)]
        public float $roundedHalfUp,
        #[RoundHalfDown(precision: 2)]
        public float $roundedHalfDown,
        #[RoundHalfEven(precision: 2)]
        public float $roundedHalfEven,
        #[RoundCeiling(precision: 2)]
        public float $roundedCeiling,
        #[RoundFloor(precision: 2)]
        public float $roundedFloor,
        #[Ceil()]
        public int $ceiled,
        #[Floor()]
        public int $floored,
        #[Clamp(min: 10, max: 20)]
        public int $clamped,
        #[Abs()]
        public int $absolute,
    ) {}
}
