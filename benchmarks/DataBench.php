<?php declare(strict_types=1);

namespace Benchmarks;

use Benchmarks\Fixtures\ComplicatedBenchData;
use Benchmarks\Support\AbstractStructBench;
use Illuminate\Support\Collection;
use PhpBench\Attributes\Assert;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;

final class DataBench extends AbstractStructBench
{
    #[
        Revs(500),
        Iterations(5),
        BeforeMethods(['setupCache', 'setupCollectionTransformation']),
        Assert('mode(variant.time.avg) < 20 milliseconds +/- 10%'),
    ]
    public function benchCollectionTransformation(): void
    {
        $this->collection->toArray();
    }

    #[
        Revs(5000),
        Iterations(5),
        BeforeMethods(['setupCache', 'setupObjectTransformation']),
        Assert('mode(variant.time.avg) < 1 millisecond +/- 10%'),
    ]
    public function benchObjectTransformation(): void
    {
        $this->object->toArray();
    }

    #[
        Revs(500),
        Iterations(5),
        BeforeMethods(['setupCache', 'setupCollectionCreation']),
        Assert('mode(variant.time.avg) < 30 milliseconds +/- 10%'),
    ]
    public function benchCollectionCreation(): void
    {
        ComplicatedBenchData::collectInto($this->collectionPayload, Collection::class);
    }

    #[
        Revs(5000),
        Iterations(5),
        BeforeMethods(['setupCache', 'setupObjectCreation']),
        Assert('mode(variant.time.avg) < 2 milliseconds +/- 10%'),
    ]
    public function benchObjectCreation(): void
    {
        ComplicatedBenchData::create($this->objectPayload);
    }

    #[
        Revs(500),
        Iterations(5),
        BeforeMethods(['setupCollectionTransformation']),
        Assert('mode(variant.time.avg) < 30 milliseconds +/- 10%'),
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
        Assert('mode(variant.time.avg) < 4 milliseconds +/- 10%'),
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
        Assert('mode(variant.time.avg) < 40 milliseconds +/- 10%'),
    ]
    public function benchCollectionCreationWithoutCache(): void
    {
        ComplicatedBenchData::collectInto($this->collectionPayload, Collection::class);
        $this->resetCache();
    }

    #[
        Revs(5000),
        Iterations(5),
        BeforeMethods(['setupObjectCreation']),
        Assert('mode(variant.time.avg) < 5 milliseconds +/- 10%'),
    ]
    public function benchObjectCreationWithoutCache(): void
    {
        ComplicatedBenchData::create($this->objectPayload);
        $this->resetCache();
    }
}
