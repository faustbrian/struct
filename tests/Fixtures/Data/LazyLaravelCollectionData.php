<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\AsLazyCollection;
use Cline\Struct\Attributes\CollectionResults\Contains;
use Cline\Struct\Attributes\CollectionResults\Count;
use Cline\Struct\Attributes\CollectionResults\First;
use Cline\Struct\Attributes\CollectionResults\Sum;
use Cline\Struct\Attributes\Collections\Filter;
use Cline\Struct\Attributes\Collections\Map;
use Cline\Struct\Attributes\Collections\Skip;
use Cline\Struct\Attributes\Collections\Take;
use Cline\Struct\Attributes\CollectionSources\FromJson;
use Cline\Struct\Attributes\CollectionSources\Range;
use Cline\Struct\Attributes\CollectionSources\Times;
use Cline\Struct\Attributes\CollectionSources\Wrap;
use Cline\Struct\Enums\DataListType;
use Illuminate\Support\LazyCollection;
use Tests\Fixtures\Casts\IntegerStringCast;
use Tests\Fixtures\Support\CollectionCallbacks\DoubleNumberMapper;
use Tests\Fixtures\Support\CollectionCallbacks\EvenNumberPredicate;
use Tests\Fixtures\Support\CollectionCallbacks\KeyAwareUppercaseMapper;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class LazyLaravelCollectionData extends AbstractData
{
    public function __construct(
        #[AsLazyCollection(IntegerStringCast::class)]
        public LazyCollection $numbers,
        #[AsLazyCollection(IntegerStringCast::class)]
        public LazyCollection $derivedNumbers,
        #[AsLazyCollection(DataListType::String)]
        #[Map(KeyAwareUppercaseMapper::class)]
        #[Skip(1)]
        #[Take(1)]
        public LazyCollection $mappedNames,
        #[AsLazyCollection(IntegerStringCast::class)]
        #[Map(DoubleNumberMapper::class)]
        #[Filter(EvenNumberPredicate::class)]
        public LazyCollection $evenNumbers,
        #[Wrap(value: 'Only')]
        public LazyCollection $wrapped,
        #[Range(2, 4)]
        public LazyCollection $ranged,
        #[Times(3)]
        public LazyCollection $times,
        #[FromJson(json: '["Alpha","Beta"]')]
        public LazyCollection $decoded,
        #[First(source: 'derivedNumbers')]
        public int $firstNumber,
        #[Count(source: 'derivedNumbers')]
        public int $numberCount,
        #[Sum(source: 'derivedNumbers')]
        public int $numberSum,
        #[Contains(source: 'decoded', value: 'Beta')]
        public bool $decodedContainsBeta,
    ) {}
}
