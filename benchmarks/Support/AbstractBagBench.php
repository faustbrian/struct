<?php declare(strict_types=1);

namespace Benchmarks\Support;

use Bag\BagServiceProvider;
use Bag\Collection;
use Bag\Internal\Cache;
use Benchmarks\Bag\Fixtures\ComplicatedBenchData;
use Benchmarks\Bag\Fixtures\NestedBenchData;
use Benchmarks\Bag\Fixtures\SimpleBenchData;
use DateTimeImmutable;
use Orchestra\Testbench\Concerns\CreatesApplication;

abstract class AbstractBagBench
{
    use CreatesApplication;

    protected Collection $collection;

    protected ComplicatedBenchData $object;

    protected array $collectionPayload;

    protected array $objectPayload;

    public function __construct()
    {
        $this->createApplication();
    }

    protected function getPackageProviders($app): array
    {
        return [
            BagServiceProvider::class,
        ];
    }

    public function setupCache(): void
    {
        Cache::reset();

        SimpleBenchData::from(['string' => 'hello']);
        NestedBenchData::from(['value' => ['string' => 'hello']]);
        ComplicatedBenchData::from($this->makePayload(profile: false));
    }

    public function resetCache(): void
    {
        Cache::reset();
    }

    public function setupCollectionTransformation(): void
    {
        $this->collection = ComplicatedBenchData::collect(
            array_map(
                fn (): ComplicatedBenchData => $this->makeObject(profile: false),
                range(1, 15),
            ),
        );
    }

    public function setupObjectTransformation(): void
    {
        $this->object = $this->makeObject(profile: false);
    }

    public function setupCollectionCreation(): void
    {
        $this->collectionPayload = array_map(
            fn (): array => $this->makePayload(profile: false),
            range(1, 15),
        );
    }

    public function setupObjectCreation(): void
    {
        $this->objectPayload = $this->makePayload(profile: false);
    }

    public function setupProfileCollectionTransformation(): void
    {
        $this->collection = ComplicatedBenchData::collect(
            array_map(
                fn (): ComplicatedBenchData => $this->makeObject(profile: true),
                range(1, 15),
            ),
        );
    }

    public function setupProfileObjectTransformation(): void
    {
        $this->object = $this->makeObject(profile: true);
    }

    public function setupProfileCollectionCreation(): void
    {
        $this->collectionPayload = array_map(
            fn (): array => $this->makePayload(profile: true),
            range(1, 15),
        );
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
            mixed: 42,
            explicitCast: new DateTimeImmutable('1994-05-16T12:00:00+01:00'),
            defaultCast: new DateTimeImmutable('1994-05-16T12:00:00+01:00'),
            nestedData: $profile ? null : new SimpleBenchData('hello'),
            nestedCollection: $profile
                ? null
                : Collection::make([
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
