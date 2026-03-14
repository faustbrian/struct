<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\AsDataList;
use Cline\Struct\Attributes\Collections\OnlyKeys;
use Cline\Struct\Enums\DataListType;
use Cline\Struct\Support\DataList;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class InvalidCollectionAttributeData extends AbstractData
{
    public function __construct(
        #[AsDataList(DataListType::String)]
        #[OnlyKeys(['keep'])]
        public DataList $list,
    ) {}
}
