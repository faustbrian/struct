<?php declare(strict_types=1);

namespace Benchmarks;

use Benchmarks\Fixtures\ComplicatedBenchData;
use Benchmarks\Support\AbstractStructBench;
use Illuminate\Support\Collection;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;

final class DataProfileBench extends AbstractStructBench
{
    public function __construct()
    {
        parent::__construct();

        $this->setupCache();
    }

    #[
        Revs(500),
        Iterations(5),
        BeforeMethods(['setupProfileCollectionTransformation']),
    ]
    public function benchProfileCollectionTransformation(): void
    {
        $this->collection->toArray();
    }

    #[
        Revs(5000),
        Iterations(5),
        BeforeMethods(['setupProfileObjectTransformation']),
    ]
    public function benchProfileObjectTransformation(): void
    {
        $this->object->toArray();
    }

    #[
        Revs(500),
        Iterations(5),
        BeforeMethods(['setupProfileCollectionCreation']),
    ]
    public function benchProfileCollectionCreation(): void
    {
        ComplicatedBenchData::collectInto($this->collectionPayload, Collection::class);
    }

    #[
        Revs(5000),
        Iterations(5),
        BeforeMethods(['setupProfileObjectCreation']),
    ]
    public function benchProfileObjectCreation(): void
    {
        ComplicatedBenchData::create($this->objectPayload);
    }
}
