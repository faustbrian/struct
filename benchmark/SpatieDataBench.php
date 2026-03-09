<?php declare(strict_types=1);

namespace Benchmark;

use Benchmark\Spatie\Fixtures\ComplicatedBenchData;
use Benchmark\Support\AbstractSpatieBench;
use Cline\Bench\Attributes\Before;
use Cline\Bench\Attributes\Bench;
use Cline\Bench\Attributes\Competitor;
use Cline\Bench\Attributes\Group;
use Cline\Bench\Attributes\Iterations;
use Cline\Bench\Attributes\Regression;
use Cline\Bench\Attributes\Revolutions;
use Cline\Bench\Attributes\Scenario;
use Cline\Bench\Enums\Metric;
use Illuminate\Support\Collection;

#[Scenario('baloo-data')]
#[Competitor('spatie')]
#[Group(['baloo', 'dto', 'comparison'])]
#[Iterations(5)]
final class SpatieDataBench extends AbstractSpatieBench
{
    #[
        Bench('collection-transformation'),
        Revolutions(500),
        Before(['setupCache', 'setupCollectionTransformation']),
        Regression(metric: Metric::Median, tolerance: '5%'),
    ]
    public function benchCollectionTransformation(): void
    {
        $this->collection->toArray();
    }

    #[
        Bench('object-transformation'),
        Revolutions(5000),
        Before(['setupCache', 'setupObjectTransformation']),
    ]
    public function benchObjectTransformation(): void
    {
        $this->object->toArray();
    }

    #[
        Bench('collection-creation'),
        Revolutions(500),
        Before(['setupCache', 'setupCollectionCreation']),
    ]
    public function benchCollectionCreation(): void
    {
        ComplicatedBenchData::collect($this->collectionPayload, Collection::class);
    }

    #[
        Bench('object-creation'),
        Revolutions(5000),
        Before(['setupCache', 'setupObjectCreation']),
    ]
    public function benchObjectCreation(): void
    {
        ComplicatedBenchData::from($this->objectPayload);
    }

    #[
        Bench('collection-transformation-without-cache'),
        Revolutions(500),
        Before(['setupCollectionTransformation']),
    ]
    public function benchCollectionTransformationWithoutCache(): void
    {
        $this->collection->toArray();
        $this->resetCache();
    }

    #[
        Bench('object-transformation-without-cache'),
        Revolutions(5000),
        Before(['setupObjectTransformation']),
    ]
    public function benchObjectTransformationWithoutCache(): void
    {
        $this->object->toArray();
        $this->resetCache();
    }

    #[
        Bench('collection-creation-without-cache'),
        Revolutions(500),
        Before(['setupCollectionCreation']),
    ]
    public function benchCollectionCreationWithoutCache(): void
    {
        ComplicatedBenchData::collect($this->collectionPayload, Collection::class);
        $this->resetCache();
    }

    #[
        Bench('object-creation-without-cache'),
        Revolutions(5000),
        Before(['setupObjectCreation']),
    ]
    public function benchObjectCreationWithoutCache(): void
    {
        ComplicatedBenchData::from($this->objectPayload);
        $this->resetCache();
    }
}
