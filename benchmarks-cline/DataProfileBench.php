<?php declare(strict_types=1);

namespace BenchmarksCline;

use Benchmarks\Fixtures\ComplicatedBenchData;
use Benchmarks\Support\AbstractStructBench;
use Cline\Bench\Attributes\Before;
use Cline\Bench\Attributes\Bench;
use Cline\Bench\Attributes\Competitor;
use Cline\Bench\Attributes\Group;
use Cline\Bench\Attributes\Iterations;
use Cline\Bench\Attributes\Regression;
use Cline\Bench\Attributes\Revs;
use Cline\Bench\Attributes\Scenario;
use Illuminate\Support\Collection;

#[Scenario('baloo-profile')]
#[Competitor('struct')]
#[Group(['baloo', 'dto', 'comparison'])]
#[Iterations(5)]
final class DataProfileBench extends AbstractStructBench
{
    public function __construct()
    {
        parent::__construct();

        $this->setupCache();
    }

    #[
        Bench('profile-collection-transformation'),
        Revs(500),
        Before(['setupProfileCollectionTransformation']),
        Regression(metric: 'median', tolerance: '5%'),
    ]
    public function benchProfileCollectionTransformation(): void
    {
        $this->collection->toArray();
    }

    #[
        Bench('profile-object-transformation'),
        Revs(5000),
        Before(['setupProfileObjectTransformation']),
    ]
    public function benchProfileObjectTransformation(): void
    {
        $this->object->toArray();
    }

    #[
        Bench('profile-collection-creation'),
        Revs(500),
        Before(['setupProfileCollectionCreation']),
    ]
    public function benchProfileCollectionCreation(): void
    {
        ComplicatedBenchData::collectInto($this->collectionPayload, Collection::class);
    }

    #[
        Bench('profile-object-creation'),
        Revs(5000),
        Before(['setupProfileObjectCreation']),
    ]
    public function benchProfileObjectCreation(): void
    {
        ComplicatedBenchData::create($this->objectPayload);
    }
}
