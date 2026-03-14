<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\AsDataCollection;
use Cline\Struct\Attributes\AsDataList;
use Cline\Struct\Attributes\Collections\ExceptKeys;
use Cline\Struct\Attributes\Collections\OnlyKeys;
use Cline\Struct\Attributes\Collections\RejectEmptyStrings;
use Cline\Struct\Attributes\Collections\RejectFalsy;
use Cline\Struct\Attributes\Collections\RejectNulls;
use Cline\Struct\Attributes\Collections\Reverse;
use Cline\Struct\Attributes\Collections\Slice;
use Cline\Struct\Attributes\Collections\SortKeys;
use Cline\Struct\Attributes\Collections\SortValues;
use Cline\Struct\Attributes\Collections\Take;
use Cline\Struct\Attributes\Collections\Unique;
use Cline\Struct\Attributes\Collections\Values;
use Cline\Struct\Enums\DataListType;
use Cline\Struct\Support\DataCollection;
use Cline\Struct\Support\DataList;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class CollectionAttributeData extends AbstractData
{
    public function __construct(
        #[Reverse()]
        public array $reversedArray,
        #[AsDataList(DataListType::Int)]
        #[Reverse()]
        public DataList $reversedList,
        #[AsDataCollection(DataListType::String)]
        #[Reverse()]
        public DataCollection $reversedCollection,
        #[RejectNulls()]
        #[RejectEmptyStrings()]
        public array $cleaned,
        #[RejectFalsy()]
        public array $truthyOnly,
        #[Unique(strict: true)]
        public array $uniqueStrict,
        #[Slice(1, 2)]
        public array $sliced,
        #[Take(2)]
        public array $taken,
        #[Values()]
        public array $reindexed,
        #[OnlyKeys(['keep', 'also'])]
        public array $onlyKeys,
        #[ExceptKeys(['drop'])]
        public array $exceptKeys,
        #[SortValues(descending: true)]
        public array $sortedValues,
        #[SortKeys()]
        public array $sortedKeys,
        #[RejectNulls()]
        #[Values()]
        #[Take(2)]
        public array $stacked,
    ) {}
}
