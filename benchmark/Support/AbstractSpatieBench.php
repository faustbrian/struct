<?php declare(strict_types=1);

namespace Benchmark\Support;

use Benchmark\Spatie\Fixtures\ComplicatedBenchData;
use Benchmark\Spatie\Fixtures\NestedBenchData;
use Benchmark\Spatie\Fixtures\SimpleBenchData;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Illuminate\Support\Collection;
use Orchestra\Testbench\Concerns\CreatesApplication;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\LaravelDataServiceProvider;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\DataConfig;

abstract class AbstractSpatieBench
{
    use CreatesApplication;

    protected DataCollection $collection;

    protected ComplicatedBenchData $object;

    protected array $collectionPayload;

    protected array $objectPayload;

    private DataConfig $dataConfig;

    public function __construct()
    {
        $this->createApplication();
        $this->dataConfig = app(DataConfig::class);
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelDataServiceProvider::class,
        ];
    }

    public function setupCache(): void
    {
        $this->dataConfig->getDataClass(ComplicatedBenchData::class)->prepareForCache();
        $this->dataConfig->getDataClass(SimpleBenchData::class)->prepareForCache();
        $this->dataConfig->getDataClass(NestedBenchData::class)->prepareForCache();
    }

    public function resetCache(): void
    {
        $this->dataConfig->reset();
    }

    public function setupCollectionTransformation(): void
    {
        $this->collection = new DataCollection(
            ComplicatedBenchData::class,
            Collection::times(
                15,
                fn (): ComplicatedBenchData => $this->makeObject(profile: false),
            ),
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
            ComplicatedBenchData::class,
            Collection::times(
                15,
                fn (): ComplicatedBenchData => $this->makeObject(profile: true),
            ),
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
            optionalInt: Optional::create(),
            mixed: 42,
            explicitCast: CarbonImmutable::create(1994, 5, 16),
            defaultCast: new DateTimeImmutable('1994-05-16T12:00:00+01:00'),
            nestedData: $profile ? null : new SimpleBenchData('hello'),
            nestedCollection: $profile
                ? null
                : new DataCollection(NestedBenchData::class, [
                    new NestedBenchData(new SimpleBenchData('I')),
                    new NestedBenchData(new SimpleBenchData('am')),
                    new NestedBenchData(new SimpleBenchData('groot')),
                ]),
            nestedArray: $profile ? [] : ['never', 'gonna', 'give', 'you', 'up'],
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
