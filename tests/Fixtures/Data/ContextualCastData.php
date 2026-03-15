<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\CastWith;
use Tests\Fixtures\Casts\ContextualValueCast;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class ContextualCastData extends AbstractData
{
    public function __construct(
        public string $mode,
        public string $prefix,
        #[CastWith(ContextualValueCast::class)]
        public string $value,
    ) {}
}
