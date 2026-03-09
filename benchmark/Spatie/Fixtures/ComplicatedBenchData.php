<?php declare(strict_types=1);

namespace Benchmark\Spatie\Fixtures;

use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Optional;

final class ComplicatedBenchData extends Data
{
    public function __construct(
        public mixed $withoutType,
        public int $int,
        public bool $bool,
        public float $float,
        public string $string,
        public array $array,
        public ?string $nullable,
        public Optional|int $optionalInt = new Optional(),
        public mixed $mixed = null,
        public CarbonImmutable $explicitCast = new CarbonImmutable(),
        public DateTimeImmutable $defaultCast = new DateTimeImmutable(),
        public ?SimpleBenchData $nestedData = null,
        #[DataCollectionOf(NestedBenchData::class)]
        public ?DataCollection $nestedCollection = null,
        /** @var array<int, string> */
        public array $nestedArray = [],
    ) {}
}
