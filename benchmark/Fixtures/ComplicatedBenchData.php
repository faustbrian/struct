<?php declare(strict_types=1);

namespace Benchmark\Fixtures;

use Carbon\CarbonImmutable;
use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\AsDataCollection;
use Cline\Struct\Attributes\AsDataList;
use Cline\Struct\Enums\DataListType;
use Cline\Struct\Support\DataCollection;
use Cline\Struct\Support\DataList;
use Cline\Struct\Support\Optional;
use DateTimeImmutable;

final readonly class ComplicatedBenchData extends AbstractData
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
        #[AsDataCollection(NestedBenchData::class)]
        public ?DataCollection $nestedCollection = null,
        #[AsDataList(DataListType::String)]
        public DataList $nestedArray = new DataList(),
    ) {}
}
