<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\AsCollection;
use Cline\Struct\Attributes\Collections\ChunkWhile;
use Cline\Struct\Attributes\Collections\Collapse;
use Cline\Struct\Attributes\Collections\CollapseWithKeys;
use Cline\Struct\Attributes\Collections\Concat;
use Cline\Struct\Attributes\Collections\Duplicates;
use Cline\Struct\Attributes\Collections\DuplicatesStrict;
use Cline\Struct\Attributes\Collections\Flatten;
use Cline\Struct\Attributes\Collections\Map;
use Cline\Struct\Attributes\Collections\MapToGroups;
use Cline\Struct\Attributes\Collections\Pluck;
use Cline\Struct\Attributes\Collections\SortKeysDesc;
use Cline\Struct\Attributes\Collections\SortKeysUsing;
use Cline\Struct\Attributes\Collections\UniqueStrict;
use Cline\Struct\Attributes\Collections\Unless;
use Cline\Struct\Attributes\Collections\UnlessEmpty;
use Cline\Struct\Attributes\Collections\UnlessNotEmpty;
use Cline\Struct\Attributes\Collections\When;
use Cline\Struct\Attributes\Collections\WhenEmpty;
use Cline\Struct\Attributes\Collections\WhenNotEmpty;
use Cline\Struct\Attributes\Collections\Where;
use Cline\Struct\Attributes\Collections\WhereBetween;
use Cline\Struct\Attributes\Collections\WhereIn;
use Cline\Struct\Attributes\Collections\WhereInStrict;
use Cline\Struct\Attributes\Collections\WhereNotBetween;
use Cline\Struct\Attributes\Collections\WhereNotIn;
use Cline\Struct\Attributes\Collections\WhereNotInStrict;
use Cline\Struct\Attributes\Collections\WhereNotNull;
use Cline\Struct\Attributes\Collections\WhereNull;
use Cline\Struct\Attributes\Collections\WhereStrict;
use Cline\Struct\Attributes\Collections\Zip;
use Cline\Struct\Enums\DataListType;
use Illuminate\Support\Collection;
use Tests\Fixtures\Support\CollectionCallbacks\AlphaChunkBoundary;
use Tests\Fixtures\Support\CollectionCallbacks\AscendingKeyComparator;
use Tests\Fixtures\Support\CollectionCallbacks\FirstLetterKeyValueMap;
use Tests\Fixtures\Support\CollectionCallbacks\HasMultipleItemsCondition;
use Tests\Fixtures\Support\CollectionCallbacks\KeyAwareUppercaseMapper;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class LaravelCollectionExtendedData extends AbstractData
{
    public function __construct(
        #[AsCollection(DataListType::Array)]
        #[Where('type', 'post')]
        public Collection $where,
        #[AsCollection(DataListType::Array)]
        #[WhereStrict('id', 1)]
        public Collection $whereStrict,
        #[AsCollection(DataListType::Array)]
        #[WhereIn('type', ['post', 'page'])]
        public Collection $whereIn,
        #[AsCollection(DataListType::Array)]
        #[WhereInStrict('id', [1, 3])]
        public Collection $whereInStrict,
        #[AsCollection(DataListType::Array)]
        #[WhereNotIn('type', ['page'])]
        public Collection $whereNotIn,
        #[AsCollection(DataListType::Array)]
        #[WhereNotInStrict('id', [2])]
        public Collection $whereNotInStrict,
        #[AsCollection(DataListType::Array)]
        #[WhereNull('deleted_at')]
        public Collection $whereNull,
        #[AsCollection(DataListType::Array)]
        #[WhereNotNull('deleted_at')]
        public Collection $whereNotNull,
        #[AsCollection(DataListType::Array)]
        #[WhereBetween('score', [10, 20])]
        public Collection $whereBetween,
        #[AsCollection(DataListType::Array)]
        #[WhereNotBetween('score', [10, 20])]
        public Collection $whereNotBetween,
        #[AsCollection(DataListType::Array)]
        #[Pluck('name', 'id')]
        public Collection $plucked,
        #[AsCollection(DataListType::Mixed)]
        #[Flatten(2)]
        public Collection $flattened,
        #[AsCollection(DataListType::Array)]
        #[Collapse()]
        public Collection $collapsed,
        #[AsCollection(DataListType::Array)]
        #[CollapseWithKeys()]
        public Collection $collapsedWithKeys,
        #[AsCollection(DataListType::String)]
        #[ChunkWhile(AlphaChunkBoundary::class)]
        public Collection $chunkedWhile,
        #[AsCollection(DataListType::String)]
        #[MapToGroups(FirstLetterKeyValueMap::class)]
        public Collection $mappedToGroups,
        #[AsCollection(DataListType::String)]
        #[SortKeysDesc()]
        public Collection $sortKeysDescending,
        #[AsCollection(DataListType::String)]
        #[SortKeysUsing(AscendingKeyComparator::class)]
        public Collection $sortKeysUsing,
        #[AsCollection(DataListType::Mixed)]
        #[UniqueStrict()]
        public Collection $uniqueStrict,
        #[AsCollection(DataListType::Mixed)]
        #[Duplicates()]
        public Collection $duplicates,
        #[AsCollection(DataListType::Mixed)]
        #[DuplicatesStrict()]
        public Collection $duplicatesStrict,
        #[AsCollection(DataListType::String)]
        #[Zip([1, 2, 3])]
        public Collection $zipped,
        #[AsCollection(DataListType::String)]
        #[When(HasMultipleItemsCondition::class)]
        #[Map(KeyAwareUppercaseMapper::class)]
        public Collection $whenMapped,
        #[AsCollection(DataListType::String)]
        #[Unless(HasMultipleItemsCondition::class)]
        #[Map(KeyAwareUppercaseMapper::class)]
        public Collection $unlessMapped,
        #[AsCollection(DataListType::String)]
        #[WhenEmpty()]
        #[Concat(['fallback'])]
        public Collection $whenEmpty,
        #[AsCollection(DataListType::String)]
        #[WhenNotEmpty()]
        #[Concat(['fallback'])]
        public Collection $whenNotEmpty,
        #[AsCollection(DataListType::String)]
        #[UnlessEmpty()]
        #[Concat(['fallback'])]
        public Collection $unlessEmpty,
        #[AsCollection(DataListType::String)]
        #[UnlessNotEmpty()]
        #[Concat(['fallback'])]
        public Collection $unlessNotEmpty,
    ) {}
}
