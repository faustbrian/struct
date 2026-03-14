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
use Cline\Struct\Attributes\Collections\Each;
use Cline\Struct\Attributes\Collections\Filter;
use Cline\Struct\Attributes\Collections\FlatMap;
use Cline\Struct\Attributes\Collections\GroupBy;
use Cline\Struct\Attributes\Collections\KeyBy;
use Cline\Struct\Attributes\Collections\Map;
use Cline\Struct\Attributes\Collections\Partition;
use Cline\Struct\Attributes\Collections\Reject;
use Cline\Struct\Attributes\Collections\SortBy;
use Cline\Struct\Attributes\Collections\Values;
use Cline\Struct\Enums\DataListType;
use Illuminate\Support\Collection;
use Tests\Fixtures\Support\CollectionCallbacks\DoubleNumberMapper;
use Tests\Fixtures\Support\CollectionCallbacks\EvenNumberPredicate;
use Tests\Fixtures\Support\CollectionCallbacks\ExplodeWordsMapper;
use Tests\Fixtures\Support\CollectionCallbacks\FirstLetterGroupKey;
use Tests\Fixtures\Support\CollectionCallbacks\KeyAwareUppercaseMapper;
use Tests\Fixtures\Support\CollectionCallbacks\RecordValueAction;
use Tests\Fixtures\Support\CollectionCallbacks\StringLengthSortValue;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class LaravelCollectionCallbackData extends AbstractData
{
    public function __construct(
        #[AsCollection(DataListType::Int)]
        #[Filter(EvenNumberPredicate::class)]
        public Collection $filtered,
        #[AsCollection(DataListType::Int)]
        #[Reject(EvenNumberPredicate::class)]
        public Collection $rejected,
        #[AsCollection(DataListType::Int)]
        #[Map(DoubleNumberMapper::class)]
        public Collection $mapped,
        #[AsCollection(DataListType::String)]
        #[FlatMap(ExplodeWordsMapper::class)]
        public Collection $flatMapped,
        #[AsCollection(DataListType::String)]
        #[SortBy(StringLengthSortValue::class, descending: true)]
        public Collection $sorted,
        #[AsCollection(DataListType::String)]
        #[GroupBy(FirstLetterGroupKey::class, preserveKeys: true)]
        public Collection $grouped,
        #[AsCollection(DataListType::String)]
        #[KeyBy(FirstLetterGroupKey::class)]
        public Collection $keyed,
        #[AsCollection(DataListType::Int)]
        #[Partition(EvenNumberPredicate::class, truthyKey: 'even', falsyKey: 'odd')]
        public Collection $partitioned,
        #[AsCollection(DataListType::String)]
        #[Values()]
        #[Map(KeyAwareUppercaseMapper::class)]
        public Collection $ordered,
        #[AsCollection(DataListType::String)]
        #[Each(RecordValueAction::class)]
        public Collection $recorded,
    ) {}
}
