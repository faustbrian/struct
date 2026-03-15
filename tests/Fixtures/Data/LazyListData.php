<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\AsLazyDataList;
use Cline\Struct\Support\LazyDataList;
use Tests\Fixtures\Casts\IntegerStringCast;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class LazyListData extends AbstractData
{
    public function __construct(
        #[AsLazyDataList(IntegerStringCast::class)]
        public LazyDataList $numbers,
    ) {}
}
