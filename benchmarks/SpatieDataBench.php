<?php declare(strict_types=1);

namespace Benchmarks;

use Benchmarks\Spatie\Fixtures\ComplicatedBenchData;
use Benchmarks\Support\AbstractSpatieBench;
use Illuminate\Support\Collection;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;

final class SpatieDataBench extends AbstractSpatieBench
{
    #[
        Revs(500),
        Iterations(5),
        BeforeMethods(['setupCache', 'setupCollectionTransformation']),
    ]
    public function benchCollectionTransformation(): void
    {
        $this->collection->toArray();
    }

    #[
        Revs(5000),
        Iterations(5),
        BeforeMethods(['setupCache', 'setupObjectTransformation']),
    ]
    public function benchObjectTransformation(): void
    {
        $this->object->toArray();
    }

    #[
        Revs(500),
        Iterations(5),
        BeforeMethods(['setupCache', 'setupCollectionCreation']),
    ]
    public function benchCollectionCreation(): void
    {
        ComplicatedBenchData::collect($this->collectionPayload, Collection::class);
    }

    #[
        Revs(5000),
        Iterations(5),
        BeforeMethods(['setupCache', 'setupObjectCreation']),
    ]
    public function benchObjectCreation(): void
    {
        ComplicatedBenchData::from($this->objectPayload);
    }

    #[
        Revs(500),
        Iterations(5),
        BeforeMethods(['setupCollectionTransformation']),
    ]
    public function benchCollectionTransformationWithoutCache(): void
    {
        $this->collection->toArray();
        $this->resetCache();
    }

    #[
        Revs(5000),
        Iterations(5),
        BeforeMethods(['setupObjectTransformation']),
    ]
    public function benchObjectTransformationWithoutCache(): void
    {
        $this->object->toArray();
        $this->resetCache();
    }

    #[
        Revs(500),
        Iterations(5),
        BeforeMethods(['setupCollectionCreation']),
    ]
    public function benchCollectionCreationWithoutCache(): void
    {
        ComplicatedBenchData::collect($this->collectionPayload, Collection::class);
        $this->resetCache();
    }

    #[
        Revs(5000),
        Iterations(5),
        BeforeMethods(['setupObjectCreation']),
    ]
    public function benchObjectCreationWithoutCache(): void
    {
        ComplicatedBenchData::from($this->objectPayload);
        $this->resetCache();
    }
}
