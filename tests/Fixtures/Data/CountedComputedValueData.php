<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\Computed;
use Tests\Fixtures\Support\CountingValueComputer;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class CountedComputedValueData extends AbstractData
{
    public function __construct(
        public string $value,
        #[Computed(CountingValueComputer::class)]
        public string $computed = '',
    ) {}
}
