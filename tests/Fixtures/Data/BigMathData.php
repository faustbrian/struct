<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Math\BigDecimal;
use Cline\Math\BigInteger;
use Cline\Math\BigNumber;
use Cline\Math\BigRational;
use Cline\Struct\AbstractData;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class BigMathData extends AbstractData
{
    public function __construct(
        public BigInteger $integer,
        public BigDecimal $decimal,
        public BigRational $rational,
        public BigNumber $number,
    ) {}
}
