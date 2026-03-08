<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Eloquent\AsData;
use Cline\Struct\Eloquent\AsDataCollection;
use Cline\Struct\Serialization\SerializationOptions;
use Cline\Struct\Support\DataCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Tests\Fixtures\Data\CountedComputedValueData;
use Tests\Fixtures\Data\FactoryUserData;
use Tests\Fixtures\Data\ObservedSerializationContextData;
use Tests\Fixtures\Data\ObservedSerializationOptionsData;
use Tests\Fixtures\Data\ValueData;
use Tests\Fixtures\Models\Article;
use Tests\Fixtures\Support\ArrayableUser;
use Tests\Fixtures\Support\CountingValueComputer;
use Tests\Fixtures\Support\SerializationContextTracker;
use Tests\Fixtures\Support\SerializationOptionsTracker;

describe('Eloquent DTO casts', function (): void {
    beforeEach(function (): void {
        SerializationOptionsTracker::$seen = [];
        SerializationContextTracker::$seen = [];

        CountingValueComputer::$instances = 0;

        Schema::create('articles', function ($table): void {
            $table->id();
            $table->json('author')->nullable();
            $table->json('contributors')->nullable();
            $table->timestamps();
        });
    });

    describe('Happy Paths', function (): void {
        test('casts dto attributes from database rows back into DTO objects', function (): void {
            // Arrange
            $article = Article::query()->create([
                'author' => FactoryUserData::create([
                    'name' => 'Brian',
                    'verified' => true,
                ]),
                'contributors' => [
                    FactoryUserData::create(['name' => 'A', 'verified' => false]),
                    FactoryUserData::create(['name' => 'B', 'verified' => true]),
                ],
            ]);

            // Act
            $fresh = Article::query()->findOrFail($article->getKey());

            // Assert
            expect($fresh->author)->toBeInstanceOf(FactoryUserData::class)
                ->and($fresh->author->name)->toBe('Brian')
                ->and($fresh->contributors)->toBeInstanceOf(DataCollection::class)
                ->and($fresh->contributors)->toHaveCount(2)
                ->and($fresh->contributors[1])->toBeInstanceOf(FactoryUserData::class);
        });

        test('hydrates single DTO values from supported caster input types', function (): void {
            // Arrange
            $caster = new AsData(FactoryUserData::class);
            $valueCaster = new AsData(ValueData::class);

            // Act
            $fromNull = $caster->get(
                new Article(),
                'author',
                null,
                [],
            );
            $fromJson = $caster->get(
                new Article(),
                'author',
                '{"name":"Json","verified":true}',
                [],
            );
            $fromScalarJson = $valueCaster->get(
                new Article(),
                'value',
                '"ignored"',
                [],
            );

            // Assert
            expect($fromNull)->toBeNull();
            expect($fromJson)->toBeInstanceOf(FactoryUserData::class)
                ->and($fromJson->name)->toBe('Json')
                ->and($fromJson->verified)->toBeTrue();
            expect($fromScalarJson)->toBeInstanceOf(ValueData::class)
                ->and($fromScalarJson->value)->toBeNull();
        });

        test('hydrates DTO collections from all supported caster branches', function (): void {
            // Arrange
            $caster = AsDataCollection::of(FactoryUserData::class);
            $valueCaster = AsDataCollection::of(ValueData::class);

            // Act
            $fromNull = $caster->get(
                new Article(),
                'contributors',
                null,
                [],
            );
            $fromJson = $valueCaster->get(
                new Article(),
                'values',
                '[{"value":"Json"},"ignored"]',
                [],
            );
            $fromScalarJson = $valueCaster->get(
                new Article(),
                'values',
                '"ignored"',
                [],
            );

            // Assert
            expect($fromNull)->toBeInstanceOf(DataCollection::class)
                ->and($fromNull)->toHaveCount(0);
            expect($fromJson)->toHaveCount(2)
                ->and($fromJson[0])->toBeInstanceOf(ValueData::class)
                ->and($fromJson[0]->value)->toBe('Json')
                ->and($fromJson[1])->toBeInstanceOf(ValueData::class)
                ->and($fromJson[1]->value)->toBeNull();
            expect($fromScalarJson)->toHaveCount(0);
        });

        test('reuses one creation context for collection cast hydration batches', function (): void {
            $caster = AsDataCollection::of(CountedComputedValueData::class);

            $actual = $caster->get(
                new Article(),
                'values',
                '[{"value":"A"},{"value":"B"}]',
                [],
            );

            expect($actual)->toHaveCount(2)
                ->and($actual[0]->computed)->toBe('A')
                ->and($actual[1]->computed)->toBe('B')
                ->and(CountingValueComputer::$instances)->toBe(1);
        });
    });

    describe('Edge Cases', function (): void {
        test('stores DTO values from mixed single-value and collection payload formats', function (): void {
            // Arrange
            $factoryUserCaster = new AsData(FactoryUserData::class);
            $valueCaster = new AsData(ValueData::class);
            $caster = AsDataCollection::of(FactoryUserData::class);
            $collectionCaster = AsDataCollection::of(ValueData::class);

            // Act
            $nullValue = $factoryUserCaster->set(
                new Article(),
                'author',
                null,
                [],
            );
            $arrayableValue = $factoryUserCaster->set(
                new Article(),
                'author',
                new ArrayableUser('Arrayable', true),
                [],
            );
            $jsonStringValue = $factoryUserCaster->set(
                new Article(),
                'author',
                '{"name":"Json String","verified":false}',
                [],
            );
            $arrayValue = $factoryUserCaster->set(
                new Article(),
                'author',
                ['name' => 'Array', 'verified' => true],
                [],
            );
            $serializeDto = $factoryUserCaster->serialize(
                new Article(),
                'author',
                FactoryUserData::create([
                    'name' => 'Serialized',
                    'verified' => true,
                ]),
                [],
            );
            $serializeRaw = $factoryUserCaster->serialize(
                new Article(),
                'author',
                'raw-value',
                [],
            );
            $scalarValue = $valueCaster->set(
                new Article(),
                'value',
                123,
                [],
            );
            $contributorsPayload = $caster->set(
                new Article(),
                'contributors',
                new Collection([
                    FactoryUserData::create(['name' => 'Dto', 'verified' => true]),
                    new ArrayableUser('Arrayable', false),
                    '{"name":"Json Item","verified":true}',
                    ['name' => 'Array Item', 'verified' => false],
                ]),
                [],
            );
            $valuesPayload = $collectionCaster->set(
                new Article(),
                'values',
                [123],
                [],
            );
            $serializedContributors = $caster->serialize(
                new Article(),
                'contributors',
                new DataCollection([
                    FactoryUserData::create(['name' => 'Serialized', 'verified' => true]),
                    'ignored',
                ]),
                [],
            );
            $serializedRaw = $caster->serialize(
                new Article(),
                'contributors',
                'raw-value',
                [],
            );

            // Assert
            expect($nullValue)->toBe(['author' => null]);
            expect($arrayableValue)->toBe(['author' => '{"name":"Arrayable","verified":true}']);
            expect($jsonStringValue)->toBe(['author' => '{"name":"Json String","verified":false}']);
            expect($arrayValue)->toBe(['author' => '{"name":"Array","verified":true}']);
            expect($serializeDto)->toBe(['name' => 'Serialized', 'verified' => true]);
            expect($serializeRaw)->toBe('raw-value');
            expect($scalarValue)->toBe(['value' => '{"value":"123"}']);
            expect($contributorsPayload)->toBe([
                'contributors' => '[{"name":"Dto","verified":true},{"name":"Arrayable","verified":false},{"name":"Json Item","verified":true},{"name":"Array Item","verified":false}]',
            ]);
            expect($valuesPayload)->toBe(['values' => '[{"value":"123"}]']);
            expect($serializedContributors)->toBe([
                ['name' => 'Serialized', 'verified' => true],
                [],
            ]);
            expect($serializedRaw)->toBe('raw-value');
        });

        test('reuses one serialization options instance for DTO collection batches', function (): void {
            $caster = AsDataCollection::of(ObservedSerializationOptionsData::class);

            $caster->set(
                new Article(),
                'values',
                new DataCollection([
                    ObservedSerializationOptionsData::create(['value' => 'A']),
                    ObservedSerializationOptionsData::create(['value' => 'B']),
                ]),
                [],
            );

            expect(SerializationOptionsTracker::$seen)->toHaveCount(2)
                ->and(SerializationOptionsTracker::$seen[0])->toBeInstanceOf(SerializationOptions::class)
                ->and(SerializationOptionsTracker::$seen[0])->toBe(SerializationOptionsTracker::$seen[1]);
        });

        test('reuses one serialization context for DTO collection batches', function (): void {
            $caster = AsDataCollection::of(ObservedSerializationContextData::class);

            $caster->set(
                new Article(),
                'values',
                new DataCollection([
                    ObservedSerializationContextData::create(['value' => 'A']),
                    ObservedSerializationContextData::create(['value' => 'B']),
                ]),
                [],
            );

            expect(SerializationContextTracker::$seen)->toHaveCount(2)
                ->and(SerializationContextTracker::$seen[0])->toBe(SerializationContextTracker::$seen[1]);
        });

        test('reuses one creation context for collection cast write batches', function (): void {
            $caster = AsDataCollection::of(CountedComputedValueData::class);

            $actual = $caster->set(
                new Article(),
                'values',
                [
                    ['value' => 'A'],
                    ['value' => 'B'],
                ],
                [],
            );

            expect($actual)->toBe([
                'values' => '[{"value":"A","computed":"A"},{"value":"B","computed":"B"}]',
            ])->and(CountingValueComputer::$instances)->toBe(1);
        });
    });

    afterEach(function (): void {
        Model::unsetEventDispatcher();
    });
});
