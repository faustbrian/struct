<?php declare(strict_types=1);

namespace Benchmarks\Bag\Fixtures;

use Bag\Attributes\Cast;
use Bag\Bag;
use Bag\Casts\CollectionOf;
use Bag\Casts\DateTime;
use Bag\Collection;
use DateTimeImmutable;

final readonly class ComplicatedBenchData extends Bag
{
    public function __construct(
        public mixed $withoutType,
        public int $int,
        public bool $bool,
        public float $float,
        public string $string,
        public array $array,
        public ?string $nullable,
        public mixed $mixed = null,
        #[Cast(DateTime::class, 'Y-m-d\TH:i:sP')]
        public DateTimeImmutable $explicitCast = new DateTimeImmutable(),
        #[Cast(DateTime::class, 'Y-m-d\TH:i:sP')]
        public DateTimeImmutable $defaultCast = new DateTimeImmutable(),
        public ?SimpleBenchData $nestedData = null,
        #[Cast(CollectionOf::class, NestedBenchData::class)]
        public ?Collection $nestedCollection = null,
        public array $nestedArray = [],
    ) {}
}
