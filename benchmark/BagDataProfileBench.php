<?php declare(strict_types=1);

namespace Benchmark;

use Benchmark\Bag\Fixtures\ComplicatedBenchData;
use Benchmark\Support\AbstractBagBench;
use Cline\Bench\Attributes\Before;
use Cline\Bench\Attributes\Bench;
use Cline\Bench\Attributes\Competitor;
use Cline\Bench\Attributes\Group;
use Cline\Bench\Attributes\Iterations;
use Cline\Bench\Attributes\Regression;
use Cline\Bench\Attributes\Revolutions;
use Cline\Bench\Attributes\Scenario;
use Cline\Bench\Enums\Metric;

#[Scenario('baloo-profile')]
#[Competitor('bag')]
#[Group(['baloo', 'dto', 'comparison'])]
#[Iterations(5)]
final class BagDataProfileBench extends AbstractBagBench
{
    public function __construct()
    {
        parent::__construct();

        $this->setupCache();
    }

    #[
        Bench('profile-collection-transformation'),
        Revolutions(500),
        Before(['setupProfileCollectionTransformation']),
        Regression(metric: Metric::Median, tolerance: '5%'),
    ]
    public function benchProfileCollectionTransformation(): void
    {
        $this->collection->toArray();
    }

    #[
        Bench('profile-object-transformation'),
        Revolutions(5000),
        Before(['setupProfileObjectTransformation']),
    ]
    public function benchProfileObjectTransformation(): void
    {
        $this->object->toArray();
    }

    #[
        Bench('profile-collection-creation'),
        Revolutions(500),
        Before(['setupProfileCollectionCreation']),
    ]
    public function benchProfileCollectionCreation(): void
    {
        ComplicatedBenchData::collect($this->collectionPayload);
    }

    #[
        Bench('profile-object-creation'),
        Revolutions(5000),
        Before(['setupProfileObjectCreation']),
    ]
    public function benchProfileObjectCreation(): void
    {
        ComplicatedBenchData::from($this->objectPayload);
    }
}
