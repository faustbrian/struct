<?php declare(strict_types=1);

namespace Benchmarks\Spatie\Fixtures;

use Spatie\LaravelData\Data;

final class SimpleBenchData extends Data
{
    public function __construct(
        public string $string,
    ) {}
}
