<?php declare(strict_types=1);

namespace Benchmark\Support;

use Benchmark\Fixtures\ComplicatedBenchData;
use Benchmark\Fixtures\NestedBenchData;
use Benchmark\Fixtures\SimpleBenchData;
use Carbon\CarbonImmutable;
use Cline\Struct\Metadata\MetadataFactory;
use Cline\Struct\StructServiceProvider;
use Cline\Struct\Support\DataCollection;
use Cline\Struct\Support\DataList;
use Cline\Struct\Support\Optional;
use DateTimeImmutable;
use Illuminate\Support\Collection;
use Orchestra\Testbench\Concerns\CreatesApplication;

abstract class AbstractStructBench
{
    use CreatesApplication;

    protected DataCollection $collection;

    protected ComplicatedBenchData $object;

    protected array $collectionPayload;

    protected array $objectPayload;

    private MetadataFactory $metadataFactory;

    public function __construct()
    {
        $this->createApplication();
        $this->metadataFactory = app(MetadataFactory::class);
    }

    protected function getPackageProviders($app): array
    {
        return [
            StructServiceProvider::class,
        ];
    }

    public function setupCache(): void
    {
        $this->metadataFactory->for(ComplicatedBenchData::class);
        $this->metadataFactory->for(SimpleBenchData::class);
        $this->metadataFactory->for(NestedBenchData::class);
    }

    public function resetCache(): void
    {
        $this->metadataFactory->clearRuntimeCache();
    }

    public function setupCollectionTransformation(): void
    {
        $this->collection = new DataCollection(
            Collection::times(
                15,
                fn (): ComplicatedBenchData => $this->makeObject(profile: false),
            )->all(),
        );
    }

    public function setupObjectTransformation(): void
    {
        $this->object = $this->makeObject(profile: false);
    }

    public function setupCollectionCreation(): void
    {
        $this->collectionPayload = Collection::times(
            15,
            fn (): array => $this->makePayload(profile: false),
        )->all();
    }

    public function setupObjectCreation(): void
    {
        $this->objectPayload = $this->makePayload(profile: false);
    }

    public function setupProfileCollectionTransformation(): void
    {
        $this->collection = new DataCollection(
            Collection::times(
                15,
                fn (): ComplicatedBenchData => $this->makeObject(profile: true),
            )->all(),
        );
    }

    public function setupProfileObjectTransformation(): void
    {
        $this->object = $this->makeObject(profile: true);
    }

    public function setupProfileCollectionCreation(): void
    {
        $this->collectionPayload = Collection::times(
            15,
            fn (): array => $this->makePayload(profile: true),
        )->all();
    }

    public function setupProfileObjectCreation(): void
    {
        $this->objectPayload = $this->makePayload(profile: true);
    }

    private function makeObject(bool $profile): ComplicatedBenchData
    {
        return new ComplicatedBenchData(
            withoutType: 42,
            int: 42,
            bool: true,
            float: 3.14,
            string: 'Hello World',
            array: [1, 1, 2, 3, 5, 8],
            nullable: null,
            optionalInt: new Optional(),
            mixed: 42,
            explicitCast: CarbonImmutable::create(1994, 5, 16),
            defaultCast: new DateTimeImmutable('1994-05-16T12:00:00+01:00'),
            nestedData: $profile ? null : new SimpleBenchData('hello'),
            nestedCollection: $profile
                ? null
                : new DataCollection([
                    new NestedBenchData(new SimpleBenchData('I')),
                    new NestedBenchData(new SimpleBenchData('am')),
                    new NestedBenchData(new SimpleBenchData('groot')),
                ]),
            nestedArray: new DataList($profile
                ? []
                : ['never', 'gonna', 'give', 'you', 'up']),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function makePayload(bool $profile): array
    {
        return [
            'withoutType' => 42,
            'int' => 42,
            'bool' => true,
            'float' => 3.14,
            'string' => 'Hello world',
            'array' => [1, 1, 2, 3, 5, 8],
            'nullable' => null,
            'mixed' => 42,
            'explicitCast' => '1994-05-16T12:00:00+01:00',
            'defaultCast' => '1994-05-16T12:00:00+01:00',
            'nestedData' => $profile
                ? null
                : [
                    'string' => 'hello',
                ],
            'nestedCollection' => $profile
                ? null
                : [
                    ['value' => ['string' => 'never']],
                    ['value' => ['string' => 'gonna']],
                    ['value' => ['string' => 'give']],
                    ['value' => ['string' => 'you']],
                    ['value' => ['string' => 'up']],
                ],
            'nestedArray' => $profile
                ? []
                : ['never', 'gonna', 'give', 'you', 'up'],
        ];
    }
}
