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
use Cline\Struct\Attributes\Collections\CrossJoin;
use Cline\Struct\Attributes\Collections\Diff;
use Cline\Struct\Attributes\Collections\DiffAssoc;
use Cline\Struct\Attributes\Collections\DiffAssocUsing;
use Cline\Struct\Attributes\Collections\DiffKeys;
use Cline\Struct\Attributes\Collections\Intersect;
use Cline\Struct\Attributes\Collections\IntersectAssoc;
use Cline\Struct\Attributes\Collections\IntersectAssocUsing;
use Cline\Struct\Attributes\Collections\IntersectByKeys;
use Cline\Struct\Attributes\Collections\IntersectUsing;
use Cline\Struct\Attributes\Collections\Merge;
use Cline\Struct\Attributes\Collections\MergeRecursive;
use Cline\Struct\Attributes\Collections\Pipe;
use Cline\Struct\Attributes\Collections\PipeInto;
use Cline\Struct\Attributes\Collections\PipeThrough;
use Cline\Struct\Attributes\Collections\Random;
use Cline\Struct\Attributes\Collections\Replace;
use Cline\Struct\Attributes\Collections\ReplaceRecursive;
use Cline\Struct\Attributes\Collections\Splice;
use Cline\Struct\Attributes\Collections\Union;
use Cline\Struct\Enums\DataListType;
use Illuminate\Support\Collection;
use Tests\Fixtures\Support\CollectionCallbacks\AppendGammaPipe;
use Tests\Fixtures\Support\CollectionCallbacks\CaseInsensitiveKeyComparator;
use Tests\Fixtures\Support\CollectionCallbacks\NumericStringComparator;
use Tests\Fixtures\Support\CollectionCallbacks\ReverseCollectionPipe;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class LaravelCollectionOperandData extends AbstractData
{
    public function __construct(
        #[AsCollection(DataListType::String)]
        public Collection $otherNames,
        #[AsCollection(DataListType::String)]
        #[Diff(source: 'otherNames')]
        public Collection $diffed,
        #[AsCollection(DataListType::String)]
        public Collection $otherAssoc,
        #[AsCollection(DataListType::String)]
        #[DiffAssoc(source: 'otherAssoc')]
        public Collection $diffAssoced,
        #[AsCollection(DataListType::String)]
        public Collection $otherAssocUsing,
        #[AsCollection(DataListType::String)]
        #[DiffAssocUsing(CaseInsensitiveKeyComparator::class, source: 'otherAssocUsing')]
        public Collection $diffAssocUsing,
        #[AsCollection(DataListType::String)]
        public Collection $otherKeys,
        #[AsCollection(DataListType::String)]
        #[DiffKeys(source: 'otherKeys')]
        public Collection $diffKeys,
        #[AsCollection(DataListType::Mixed)]
        #[CrossJoin(values: [1, 2])]
        public Collection $crossJoined,
        #[AsCollection(DataListType::Mixed)]
        #[Intersect(values: ['Beta', 'Gamma'])]
        public Collection $intersected,
        #[AsCollection(DataListType::Mixed)]
        #[IntersectUsing(NumericStringComparator::class, values: [2, 4])]
        public Collection $intersectUsing,
        #[AsCollection(DataListType::String)]
        #[IntersectAssoc(values: ['keep' => 'Alpha'])]
        public Collection $intersectAssoc,
        #[AsCollection(DataListType::String)]
        public Collection $otherAssocIntersectUsing,
        #[AsCollection(DataListType::String)]
        #[IntersectAssocUsing(CaseInsensitiveKeyComparator::class, source: 'otherAssocIntersectUsing')]
        public Collection $intersectAssocUsing,
        #[AsCollection(DataListType::String)]
        #[IntersectByKeys(values: ['keep' => true])]
        public Collection $intersectByKeys,
        #[AsCollection(DataListType::String)]
        #[Merge(values: ['Gamma'])]
        public Collection $merged,
        #[AsCollection(DataListType::Array)]
        #[MergeRecursive(values: ['meta' => ['active' => true]])]
        public Collection $mergedRecursive,
        #[AsCollection(DataListType::String)]
        #[Replace(values: ['keep' => 'Replaced'])]
        public Collection $replaced,
        #[AsCollection(DataListType::Array)]
        #[ReplaceRecursive(values: ['meta' => ['active' => true]])]
        public Collection $replacedRecursive,
        #[AsCollection(DataListType::String)]
        #[Union(values: ['keep' => 'Ignored', 'new' => 'Gamma'])]
        public Collection $unioned,
        #[AsCollection(DataListType::String)]
        #[Random(2)]
        public Collection $randomized,
        #[AsCollection(DataListType::Int)]
        #[Splice(1, 2)]
        public Collection $spliced,
        #[AsCollection(DataListType::String)]
        #[Pipe(AppendGammaPipe::class)]
        public Collection $piped,
        #[AsCollection(DataListType::String)]
        #[PipeInto(AppendGammaPipe::class)]
        public Collection $pipedInto,
        #[AsCollection(DataListType::String)]
        #[PipeThrough([AppendGammaPipe::class, ReverseCollectionPipe::class])]
        public Collection $pipedThrough,
    ) {}
}
