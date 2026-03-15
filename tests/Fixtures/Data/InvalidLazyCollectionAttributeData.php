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
use Cline\Struct\Attributes\Collections\Reverse;
use Cline\Struct\Enums\DataListType;
use Cline\Struct\Support\LazyDataList;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class InvalidLazyCollectionAttributeData extends AbstractData
{
    public function __construct(
        #[AsLazyDataList(DataListType::Int)]
        #[Reverse()]
        public LazyDataList $list,
    ) {}
}
