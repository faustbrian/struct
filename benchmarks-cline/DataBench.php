<?php declare(strict_types=1);

namespace BenchmarksCline;

use Benchmarks\Fixtures\ComplicatedBenchData;
use Benchmarks\Support\AbstractStructBench;
use Cline\Bench\Attributes\Assert;
use Cline\Bench\Attributes\Before;
use Cline\Bench\Attributes\Bench;
use Cline\Bench\Attributes\Competitor;
use Cline\Bench\Attributes\Group;
use Cline\Bench\Attributes\Iterations;
use Cline\Bench\Attributes\Regression;
use Cline\Bench\Attributes\Revs;
use Cline\Bench\Attributes\Scenario;
use Illuminate\Support\Collection;

#[Scenario('baloo-data')]
#[Competitor('struct')]
#[Group(['baloo', 'dto', 'comparison'])]
#[Iterations(5)]
final class DataBench extends AbstractStructBench
{
    #[
        Bench('collection-transformation'),
        Revs(500),
        Before(['setupCache', 'setupCollectionTransformation']),
        Regression(metric: 'median', tolerance: '5%'),
        Assert('median', '<', 40_000_000.0),
    ]
    public function benchCollectionTransformation(): void
    {
        $this->collection->toArray();
    }

    #[
        Bench('object-transformation'),
        Revs(5000),
        Before(['setupCache', 'setupObjectTransformation']),
        Assert('median', '<', 8_000_000.0),
    ]
    public function benchObjectTransformation(): void
    {
        $this->object->toArray();
    }

    #[
        Bench('collection-creation'),
        Revs(500),
        Before(['setupCache', 'setupCollectionCreation']),
        Assert('median', '<', 50_000_000.0),
    ]
    public function benchCollectionCreation(): void
    {
        ComplicatedBenchData::collectInto($this->collectionPayload, Collection::class);
    }

    #[
        Bench('object-creation'),
        Revs(5000),
        Before(['setupCache', 'setupObjectCreation']),
        Assert('median', '<', 10_000_000.0),
    ]
    public function benchObjectCreation(): void
    {
        ComplicatedBenchData::create($this->objectPayload);
    }

    #[
        Bench('collection-transformation-without-cache'),
        Revs(500),
        Before(['setupCollectionTransformation']),
        Assert('median', '<', 60_000_000.0),
    ]
    public function benchCollectionTransformationWithoutCache(): void
    {
        $this->collection->toArray();
        $this->resetCache();
    }

    #[
        Bench('object-transformation-without-cache'),
        Revs(5000),
        Before(['setupObjectTransformation']),
        Assert('median', '<', 12_000_000.0),
    ]
    public function benchObjectTransformationWithoutCache(): void
    {
        $this->object->toArray();
        $this->resetCache();
    }

    #[
        Bench('collection-creation-without-cache'),
        Revs(500),
        Before(['setupCollectionCreation']),
        Assert('median', '<', 70_000_000.0),
    ]
    public function benchCollectionCreationWithoutCache(): void
    {
        ComplicatedBenchData::collectInto($this->collectionPayload, Collection::class);
        $this->resetCache();
    }

    #[
        Bench('object-creation-without-cache'),
        Revs(5000),
        Before(['setupObjectCreation']),
        Assert('median', '<', 15_000_000.0),
    ]
    public function benchObjectCreationWithoutCache(): void
    {
        ComplicatedBenchData::create($this->objectPayload);
        $this->resetCache();
    }
}
