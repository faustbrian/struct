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
use Cline\Struct\Attributes\CollectionResults\Average;
use Cline\Struct\Attributes\CollectionResults\Avg;
use Cline\Struct\Attributes\CollectionResults\Contains;
use Cline\Struct\Attributes\CollectionResults\ContainsStrict;
use Cline\Struct\Attributes\CollectionResults\Count;
use Cline\Struct\Attributes\CollectionResults\DoesntContain;
use Cline\Struct\Attributes\CollectionResults\DoesntContainStrict;
use Cline\Struct\Attributes\CollectionResults\Every;
use Cline\Struct\Attributes\CollectionResults\First;
use Cline\Struct\Attributes\CollectionResults\FirstWhere;
use Cline\Struct\Attributes\CollectionResults\Implode;
use Cline\Struct\Attributes\CollectionResults\Join;
use Cline\Struct\Attributes\CollectionResults\Last;
use Cline\Struct\Attributes\CollectionResults\Max;
use Cline\Struct\Attributes\CollectionResults\Median;
use Cline\Struct\Attributes\CollectionResults\Min;
use Cline\Struct\Attributes\CollectionResults\Mode;
use Cline\Struct\Attributes\CollectionResults\Percentage;
use Cline\Struct\Attributes\CollectionResults\Pop;
use Cline\Struct\Attributes\CollectionResults\Pull;
use Cline\Struct\Attributes\CollectionResults\Reduce;
use Cline\Struct\Attributes\CollectionResults\ReduceSpread;
use Cline\Struct\Attributes\CollectionResults\Search;
use Cline\Struct\Attributes\CollectionResults\Shift;
use Cline\Struct\Attributes\CollectionResults\Sole;
use Cline\Struct\Attributes\CollectionResults\Some;
use Cline\Struct\Attributes\CollectionResults\Sum;
use Cline\Struct\Attributes\CollectionResults\Unwrap;
use Cline\Struct\Attributes\CollectionResults\Value;
use Cline\Struct\Attributes\Collections\Combine;
use Cline\Struct\Attributes\Collections\Forget;
use Cline\Struct\Attributes\CollectionSources\Range;
use Cline\Struct\Attributes\CollectionSources\Times;
use Cline\Struct\Attributes\CollectionSources\Wrap;
use Cline\Struct\Enums\DataListType;
use Illuminate\Support\Collection;
use Tests\Fixtures\Support\CollectionCallbacks\EvenNumberPredicate;
use Tests\Fixtures\Support\CollectionCallbacks\EvenOddSpreadReducer;
use Tests\Fixtures\Support\CollectionCallbacks\SumCarryReducer;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class LaravelCollectionDerivedData extends AbstractData
{
    public function __construct(
        #[AsCollection(DataListType::String)]
        public Collection $names,
        #[AsCollection(DataListType::Array)]
        public Collection $records,
        #[AsCollection(DataListType::Int)]
        public Collection $modalNumbers,
        #[AsCollection(DataListType::String)]
        public Collection $keyedValues,
        #[AsCollection(DataListType::String)]
        #[Combine(['Alpha', 'Beta'])]
        public Collection $combined,
        #[AsCollection(DataListType::String)]
        #[Forget('drop')]
        public Collection $forgotten,
        #[AsCollection(DataListType::Mixed)]
        #[Wrap(source: 'scalarValue')]
        public Collection $wrapped,
        #[AsCollection(DataListType::Int)]
        #[Range(2, 4)]
        public Collection $ranged,
        #[AsCollection(DataListType::Int)]
        #[Times(3)]
        public Collection $times,
        #[Contains('names', 'Alpha')]
        public bool $containsAlpha,
        #[Contains('records', 'post', 'type')]
        public bool $containsType,
        #[ContainsStrict('names', 'Alpha')]
        public bool $containsStrictAlpha,
        #[DoesntContain('names', 'Gamma')]
        public bool $doesntContainGamma,
        #[DoesntContainStrict('names', 'gamma')]
        public bool $doesntContainStrictGamma,
        #[Every('ranged', EvenNumberPredicate::class)]
        public bool $everyEven,
        #[Some('ranged', EvenNumberPredicate::class)]
        public bool $someEven,
        #[First('names')]
        public string $firstName,
        #[Last('names')]
        public string $lastName,
        #[Sole('wrapped')]
        public string $soleWrappedValue,
        #[FirstWhere('records', 'type', 'page')]
        public array $firstPageRecord,
        #[Search('names', 'Beta')]
        public int $searchBeta,
        #[Value('records', 'type')]
        public string $firstRecordType,
        #[Count('names')]
        public int $nameCount,
        #[Unwrap('names')]
        public array $unwrappedNames,
        #[Sum('ranged')]
        public int $sumRange,
        #[Min('ranged')]
        public int $minRange,
        #[Max('ranged')]
        public int $maxRange,
        #[Avg('ranged')]
        public float $avgRange,
        #[Average('ranged')]
        public float $averageRange,
        #[Median('ranged')]
        public float $medianRange,
        #[Mode('modalNumbers')]
        public array $modeValues,
        #[Percentage('ranged', EvenNumberPredicate::class)]
        public float $evenPercentage,
        #[Reduce('ranged', SumCarryReducer::class, 0)]
        public int $reducedSum,
        #[ReduceSpread('ranged', EvenOddSpreadReducer::class, [0, 0])]
        public array $reducedSpread,
        #[Implode('names', ', ')]
        public string $implodedNames,
        #[Join('names', ', ', ' and ')]
        public string $joinedNames,
        #[Pop('names')]
        public string $poppedName,
        #[Shift('names')]
        public string $shiftedName,
        #[Pull('keyedValues', 'only')]
        public string $pulledValue,
        public string $scalarValue,
    ) {}
}
