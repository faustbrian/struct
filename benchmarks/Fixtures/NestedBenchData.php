<?php declare(strict_types=1);

namespace Benchmarks\Fixtures;

use Cline\Struct\AbstractData;

final readonly class NestedBenchData extends AbstractData
{
    public function __construct(
        public SimpleBenchData $value,
    ) {}
}
