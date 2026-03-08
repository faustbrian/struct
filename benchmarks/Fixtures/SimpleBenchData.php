<?php declare(strict_types=1);

namespace Benchmarks\Fixtures;

use Cline\Struct\AbstractData;

final readonly class SimpleBenchData extends AbstractData
{
    public function __construct(
        public string $string,
    ) {}
}
