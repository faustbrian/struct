<?php declare(strict_types=1);

namespace Benchmarks\Bag\Fixtures;

use Bag\Bag;

final readonly class SimpleBenchData extends Bag
{
    public function __construct(
        public string $string,
    ) {}
}
