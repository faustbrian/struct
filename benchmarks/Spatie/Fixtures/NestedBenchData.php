<?php declare(strict_types=1);

namespace Benchmarks\Spatie\Fixtures;

use Spatie\LaravelData\Data;

final class NestedBenchData extends Data
{
    public function __construct(
        public SimpleBenchData $value,
    ) {}
}
