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
use Cline\Struct\Attributes\Collections\CountBy;
use Cline\Struct\Attributes\Collections\Dot;
use Cline\Struct\Attributes\Collections\Except;
use Cline\Struct\Attributes\Collections\Flip;
use Cline\Struct\Attributes\Collections\ForPage;
use Cline\Struct\Attributes\Collections\Keys;
use Cline\Struct\Attributes\Collections\Multiply;
use Cline\Struct\Attributes\Collections\Nth;
use Cline\Struct\Attributes\Collections\Only;
use Cline\Struct\Attributes\Collections\Pad;
use Cline\Struct\Attributes\Collections\Prepend;
use Cline\Struct\Attributes\Collections\Push;
use Cline\Struct\Attributes\Collections\Put;
use Cline\Struct\Attributes\Collections\Select;
use Cline\Struct\Attributes\Collections\Shuffle;
use Cline\Struct\Attributes\Collections\Skip;
use Cline\Struct\Attributes\Collections\Sort;
use Cline\Struct\Attributes\Collections\SortDesc;
use Cline\Struct\Attributes\Collections\Split;
use Cline\Struct\Attributes\Collections\SplitIn;
use Cline\Struct\Attributes\Collections\Transform;
use Cline\Struct\Attributes\Collections\Undot;
use Cline\Struct\Attributes\Collections\WhereInstanceOf;
use Cline\Struct\Enums\DataListType;
use Illuminate\Support\Collection;
use stdClass;
use Tests\Fixtures\Support\CollectionCallbacks\FirstLetterGroupKey;
use Tests\Fixtures\Support\CollectionCallbacks\KeyAwareUppercaseMapper;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class LaravelCollectionSelectionData extends AbstractData
{
    public function __construct(
        #[AsCollection(DataListType::Mixed)]
        #[WhereInstanceOf(stdClass::class)]
        public Collection $objectsOnly,
        #[AsCollection(DataListType::String)]
        #[CountBy(FirstLetterGroupKey::class)]
        public Collection $countedByLetter,
        #[AsCollection(DataListType::String)]
        #[Except(['drop'])]
        public Collection $excepted,
        #[AsCollection(DataListType::String)]
        #[Flip()]
        public Collection $flipped,
        #[AsCollection(DataListType::String)]
        #[ForPage(2, 2)]
        public Collection $paged,
        #[AsCollection(DataListType::String)]
        #[Keys()]
        public Collection $keysOnly,
        #[AsCollection(DataListType::Array)]
        #[Select(['name'])]
        public Collection $selected,
        #[AsCollection(DataListType::Array)]
        #[Dot()]
        public Collection $dotted,
        #[AsCollection(DataListType::Mixed)]
        #[Undot()]
        public Collection $undotted,
        #[AsCollection(DataListType::String)]
        #[Multiply(2)]
        public Collection $multiplied,
        #[AsCollection(DataListType::Int)]
        #[Nth(2, 1)]
        public Collection $nthValues,
        #[AsCollection(DataListType::String)]
        #[Only(['keep'])]
        public Collection $onlyKeys,
        #[AsCollection(DataListType::Int)]
        #[Pad(4, 0)]
        public Collection $padded,
        #[AsCollection(DataListType::String)]
        #[Prepend('Zero', 'z')]
        public Collection $prepended,
        #[AsCollection(DataListType::String)]
        #[Push(['Gamma'])]
        public Collection $pushed,
        #[AsCollection(DataListType::String)]
        #[Put('gamma', 'Gamma')]
        public Collection $putValues,
        #[AsCollection(DataListType::String)]
        #[Shuffle()]
        public Collection $shuffled,
        #[AsCollection(DataListType::Int)]
        #[Skip(2)]
        public Collection $skipped,
        #[AsCollection(DataListType::Int)]
        #[Sort()]
        public Collection $sorted,
        #[AsCollection(DataListType::Int)]
        #[SortDesc()]
        public Collection $sortedDescending,
        #[AsCollection(DataListType::Int)]
        #[Split(3)]
        public Collection $split,
        #[AsCollection(DataListType::Int)]
        #[SplitIn(2)]
        public Collection $splitIn,
        #[AsCollection(DataListType::String)]
        #[Transform(KeyAwareUppercaseMapper::class)]
        public Collection $transformed,
    ) {}
}
