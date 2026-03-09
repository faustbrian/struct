<?php declare(strict_types=1);

namespace Benchmark;

use Benchmark\Fixtures\ComplicatedBenchData;
use Benchmark\Support\AbstractStructBench;
use Cline\Bench\Attributes\Assert;
use Cline\Bench\Attributes\Before;
use Cline\Bench\Attributes\Bench;
use Cline\Bench\Attributes\Competitor;
use Cline\Bench\Attributes\Group;
use Cline\Bench\Attributes\Iterations;
use Cline\Bench\Attributes\Regression;
use Cline\Bench\Attributes\Revolutions;
use Cline\Bench\Attributes\Scenario;
use Cline\Bench\Enums\AssertionOperator;
use Cline\Bench\Enums\Metric;
use Illuminate\Support\Collection;

#[Scenario('baloo-data')]
#[Competitor('struct')]
#[Group(['baloo', 'dto', 'comparison'])]
#[Iterations(5)]
final class DataBench extends AbstractStructBench
{
    #[
        Bench('collection-transformation'),
        Revolutions(500),
        Before(['setupCache', 'setupCollectionTransformation']),
        Regression(metric: Metric::Median, tolerance: '5%'),
        Assert(Metric::Median, AssertionOperator::LessThan, 40_000_000.0),
    ]
    public function benchCollectionTransformation(): void
    {
        $this->collection->toArray();
    }

    #[
        Bench('object-transformation'),
        Revolutions(5000),
        Before(['setupCache', 'setupObjectTransformation']),
        Assert(Metric::Median, AssertionOperator::LessThan, 8_000_000.0),
    ]
    public function benchObjectTransformation(): void
    {
        $this->object->toArray();
    }

    #[
        Bench('collection-creation'),
        Revolutions(500),
        Before(['setupCache', 'setupCollectionCreation']),
        Assert(Metric::Median, AssertionOperator::LessThan, 50_000_000.0),
    ]
    public function benchCollectionCreation(): void
    {
        ComplicatedBenchData::collectInto($this->collectionPayload, Collection::class);
    }

    #[
        Bench('object-creation'),
        Revolutions(5000),
        Before(['setupCache', 'setupObjectCreation']),
        Assert(Metric::Median, AssertionOperator::LessThan, 10_000_000.0),
    ]
    public function benchObjectCreation(): void
    {
        ComplicatedBenchData::create($this->objectPayload);
    }

    #[
        Bench('collection-transformation-without-cache'),
        Revolutions(500),
        Before(['setupCollectionTransformation']),
        Assert(Metric::Median, AssertionOperator::LessThan, 60_000_000.0),
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
        Assert(Metric::Median, AssertionOperator::LessThan, 12_000_000.0),
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
        Assert(Metric::Median, AssertionOperator::LessThan, 70_000_000.0),
    ]
    public function benchCollectionCreationWithoutCache(): void
    {
        ComplicatedBenchData::collectInto($this->collectionPayload, Collection::class);
        $this->resetCache();
    }

    #[
        Bench('object-creation-without-cache'),
        Revolutions(5000),
        Before(['setupObjectCreation']),
        Assert(Metric::Median, AssertionOperator::LessThan, 15_000_000.0),
    ]
    public function benchObjectCreationWithoutCache(): void
    {
        ComplicatedBenchData::create($this->objectPayload);
        $this->resetCache();
    }
}
