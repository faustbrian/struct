<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Carbon\CarbonImmutable;
use Cline\Struct\Contracts\CastInterface;
use Cline\Struct\Eloquent\AsData;
use Cline\Struct\Eloquent\AsDataCollection;
use Cline\Struct\Exceptions\DataValidationException;
use Cline\Struct\Exceptions\InvalidArrayCollectTargetException;
use Cline\Struct\Exceptions\MissingDataValueException;
use Cline\Struct\Exceptions\SuperfluousInputKeyException;
use Cline\Struct\Exceptions\UnsupportedFactoryException;
use Cline\Struct\Factories\AbstractFactory;
use Cline\Struct\Metadata\ClassMetadata;
use Cline\Struct\Metadata\MetadataFactory;
use Cline\Struct\Metadata\PropertyMetadata;
use Cline\Struct\Serialization\SerializationContext;
use Cline\Struct\Serialization\SerializationOptions;
use Cline\Struct\Support\CreationContext;
use Cline\Struct\Support\DataCollection;
use Cline\Struct\Support\DataList;
use Cline\Struct\Support\Optional;
use Cline\Struct\Support\RecursionGuard;
use Cline\Struct\Validation\ValidationFactory;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Tests\Fixtures\Casts\IntegerStringCast;
use Tests\Fixtures\Data\ComputedUserData;
use Tests\Fixtures\Data\FactoryUserData;
use Tests\Fixtures\Data\LenientUndefinedData;
use Tests\Fixtures\Data\MappedUserData;
use Tests\Fixtures\Data\NestedPayloadData;
use Tests\Fixtures\Data\NullableNoteData;
use Tests\Fixtures\Data\ScalarValueData;
use Tests\Fixtures\Data\SongData;
use Tests\Fixtures\Data\StrictUserData;
use Tests\Fixtures\Enums\Mood;
use Tests\Fixtures\Enums\UserStatus;
use Tests\Fixtures\Support\ArrayableValue;
use Tests\Fixtures\Support\ComputedKeysComputer;

describe('AbstractData', function (): void {
    beforeEach(function (): void {
        $this->timestamp = '2026-03-07T10:00:00+00:00';

        $this->mappedPayload = [
            'id' => 1,
            'full_name' => 'Brian Faust',
            'created_at' => $this->timestamp,
            'status' => 'active',
        ];

        $this->collectionItems = [
            ['title' => 'A', 'artist' => 'Artist A'],
            ['title' => 'B', 'artist' => 'Artist B'],
        ];
    });

    describe('Happy Paths', function (): void {
        test('falls back to json when no stringifier is configured', function (): void {
            // Arrange
            $dto = SongData::create([
                'title' => 'Never Gonna Give You Up',
                'artist' => 'Rick Astley',
            ]);

            // Act
            $value = (string) $dto;

            // Assert
            expect($value)->toBe($dto->toJson());
        });

        test('uses configured stringifiers when present', function (): void {
            // Arrange
            $dto = ComputedUserData::create([
                'first_name' => 'Brian',
                'last_name' => 'Faust',
                'password' => 'secret',
            ]);

            // Act
            $value = (string) $dto;

            // Assert
            expect($value)->toContain('display_name');
        });

        test('supports successful validation and exposes cast helpers', function (): void {
            // Arrange
            $payload = $this->mappedPayload;

            // Act
            $validated = MappedUserData::createWithValidation($payload);

            // Assert
            expect($validated)->toBeInstanceOf(MappedUserData::class)
                ->and(FactoryUserData::factory())->toBeObject()
                ->and(FactoryUserData::castUsing([]))->toBeInstanceOf(AsData::class)
                ->and(FactoryUserData::castAsCollection())->toBeInstanceOf(AsDataCollection::class);
        });

        test('falls back to fresh metadata and validation factories when container fails', function (): void {
            // Arrange
            app()->bind(MetadataFactory::class, static fn (): never => throw new RuntimeException('metadata failed'));
            app()->bind(ValidationFactory::class, static fn (): never => throw new RuntimeException('validation failed'));

            // Act
            $dto = SongData::create([
                'title' => 'Fallback',
                'artist' => 'Artist',
            ]);

            // Assert
            expect($dto)->toBeInstanceOf(SongData::class)
                ->and(invokeAbstractDataMethod(SongData::class, 'validationFactory'))->toBeInstanceOf(ValidationFactory::class);
        });

        test('collects into supported container conversion targets', function (): void {
            // Arrange
            $items = $this->collectionItems;

            // Act
            $array = SongData::collectInto($items, 'array');
            $collection = SongData::collectInto(collect($items), Collection::class);
            $eloquent = SongData::collectInto(
                new EloquentCollection($items),
                EloquentCollection::class,
            );
            $lengthAware = SongData::collectInto(
                new LengthAwarePaginator($items, total: 2, perPage: 15, currentPage: 1),
                LengthAwarePaginator::class,
            );
            $cursor = SongData::collectInto(
                new CursorPaginator($items, perPage: 15),
                CursorPaginator::class,
            );

            // Assert
            expect($array)->toBeArray()
                ->and($array[0])->toBeInstanceOf(SongData::class)
                ->and($collection)->toBeInstanceOf(Collection::class)
                ->and($collection->first())->toBeInstanceOf(SongData::class)
                ->and($eloquent)->toBeInstanceOf(EloquentCollection::class)
                ->and($eloquent->first())->toBeInstanceOf(SongData::class)
                ->and($lengthAware->items()[0])->toBeInstanceOf(SongData::class)
                ->and($cursor->items()[0])->toBeInstanceOf(SongData::class);
        });

        test('maps collected items from scalar, arrayable, object, array, and dto values', function (): void {
            // Arrange
            $dto = ScalarValueData::create(['value' => 4]);
            $items = ScalarValueData::collect([
                1,
                new ArrayableValue(['value' => 2]),
                (object) ['value' => 3],
                ['value' => 4],
                $dto,
            ]);

            // Act
            $result = $items;

            // Assert
            expect($result)->toBeArray()
                ->and($result[0]->value)->toBe(1)
                ->and($result[1]->value)->toBe(2)
                ->and($result[2]->value)->toBe(3)
                ->and($result[3]->value)->toBe(4)
                ->and($result[4])->toBe($dto);
        });

        test('collects into every supported conversion branch', function (): void {
            // Arrange
            $items = $this->collectionItems;

            // Act
            $arrayIntoEloquent = SongData::collectInto($items, EloquentCollection::class);
            $collectionIntoEloquent = SongData::collectInto(collect($items), EloquentCollection::class);
            $eloquentIntoCollection = SongData::collectInto(
                new EloquentCollection($items),
                Collection::class,
            );

            // Assert
            expect($arrayIntoEloquent)->toBeInstanceOf(EloquentCollection::class)
                ->and($collectionIntoEloquent)->toBeInstanceOf(EloquentCollection::class)
                ->and($eloquentIntoCollection)->toBeInstanceOf(Collection::class);
        });

        test('ignores numeric overrides when cloning with with()', function (): void {
            // Arrange
            $dto = SongData::create([
                'title' => 'Original',
                'artist' => 'Rick Astley',
            ]);

            // Act
            $clone = $dto->with(...['ignored', 'title' => 'Changed']);

            // Assert
            expect($clone->title)->toBe('Changed')
                ->and($clone->artist)->toBe('Rick Astley');
        });

        test('serializes sensitive, optional, enum, date, dto, array, and object values', function (): void {
            // Arrange
            $computed = ComputedUserData::create([
                'first_name' => 'Brian',
                'last_name' => 'Faust',
                'password' => 'secret',
            ]);

            $nested = NestedPayloadData::create([
                'status' => 'active',
                'createdAt' => $this->timestamp,
                'song' => [
                    'title' => 'Together Forever',
                    'artist' => 'Rick Astley',
                ],
                'payload' => [
                    'enum' => UserStatus::Active,
                    'date' => CarbonImmutable::parse($this->timestamp),
                    'dto' => SongData::create([
                        'title' => 'Hold Me in Your Arms',
                        'artist' => 'Rick Astley',
                    ]),
                    'object' => (object) [
                        'nested' => UserStatus::Inactive,
                    ],
                ],
            ]);

            // Act
            $computedArray = $computed->toArray();
            $computedSensitiveArray = $computed->toArray(includeSensitive: true);
            $nestedArray = $nested->toArray();

            // Assert
            expect($computedArray)->toBe([
                'first_name' => 'Brian',
                'last_name' => 'Faust',
                'display_name' => 'Brian Faust',
            ])->and($computedSensitiveArray)->toBe([
                'first_name' => 'Brian',
                'last_name' => 'Faust',
                'password' => 'secret',
                'display_name' => 'Brian Faust',
            ])->and($nestedArray)->toBe([
                'status' => 'active',
                'createdAt' => $this->timestamp,
                'song' => [
                    'title' => 'Together Forever',
                    'artist' => 'Rick Astley',
                ],
                'payload' => [
                    'enum' => 'active',
                    'date' => $this->timestamp,
                    'dto' => [
                        'title' => 'Hold Me in Your Arms',
                        'artist' => 'Rick Astley',
                    ],
                    'object' => [
                        'nested' => 'inactive',
                    ],
                ],
            ]);
        });
    });

    describe('Sad Paths', function (): void {
        test('throws validation exception on validated creation failure', function (): void {
            // Arrange
            $payload = [
                'id' => 1,
                'full_name' => 'No',
                'created_at' => $this->timestamp,
                'status' => 'active',
            ];

            // Act & Assert
            expect(fn (): MappedUserData => MappedUserData::createWithValidation($payload))->toThrow(DataValidationException::class);
        });

        test('throws on requesting an unsupported factory', function (): void {
            // Act & Assert
            expect(fn (): AbstractFactory => SongData::factory())->toThrow(UnsupportedFactoryException::class);
        });

        test('throws on unsupported collect targets', function (): void {
            // Arrange
            $items = [
                ['title' => 'A', 'artist' => 'Artist A'],
            ];

            // Act & Assert
            expect(fn (): Collection|LengthAwarePaginator|CursorPaginator|array => SongData::collectInto($items, 'unsupported-target'))->toThrow(InvalidArrayCollectTargetException::class);
        });

        test('throws for superfluous keys and normalizes empty strings to null', function (): void {
            // Arrange
            $input = [
                'id' => 1,
                'name' => 'Brian',
                'status' => 'active',
                'extra' => 'nope',
                'created_at' => $this->timestamp,
            ];

            // Act & Assert
            expect(fn (): StrictUserData => StrictUserData::create($input))->toThrow(SuperfluousInputKeyException::class)
                ->and(MappedUserData::create([
                    'id' => 1,
                    'full_name' => 'Brian Faust',
                    'created_at' => $this->timestamp,
                    'status' => 'active',
                    'email' => '   ',
                ])->email)->toBeNull();
        });
    });

    describe('Edge Cases', function (): void {
        test('combines defaults and nullables while throwing on missing required properties', function (): void {
            // Arrange
            $factoryUser = FactoryUserData::create(['name' => 'Brian']);
            $nullable = NullableNoteData::create(['name' => 'Brian']);
            $lenient = LenientUndefinedData::create(['name' => 'Brian']);
            $optional = MappedUserData::create($this->mappedPayload);

            // Act & Assert
            expect($factoryUser->verified)->toBeFalse()
                ->and($nullable->note)->toBeNull()
                ->and($lenient->email)->toBeNull()
                ->and($optional->age)->toBeInstanceOf(Optional::class)
                ->and(fn (): SongData => SongData::create([
                    'title' => 'Only title',
                ]))->toThrow(MissingDataValueException::class);
        });

        test('covers helper branches through reflective calls', function (): void {
            // Arrange
            $dummyParameter = new ReflectionClass(SongData::class)->getConstructor()->getParameters()[0];

            $mixedProperty = makePropertyMetadata($dummyParameter, types: ['null', Optional::class], nullable: true);
            $castProperty = makePropertyMetadata(
                $dummyParameter,
                name: 'numbers',
                castClass: IntegerStringCast::class,
            );
            $listTypeProperty = makePropertyMetadata(
                $dummyParameter,
                name: 'numbers',
                dataListType: 'int',
            );
            $listCastProperty = makePropertyMetadata(
                $dummyParameter,
                name: 'numbers',
                dataListCastClass: IntegerStringCast::class,
            );
            $collectionTypeProperty = makePropertyMetadata(
                $dummyParameter,
                name: 'contacts',
                dataCollectionType: SongData::class,
            );
            $collectionCastProperty = makePropertyMetadata(
                $dummyParameter,
                name: 'contacts',
                dataCollectionCastClass: IntegerStringCast::class,
            );
            $unitEnumProperty = makePropertyMetadata($dummyParameter, types: [Mood::class]);
            $dateProperty = makePropertyMetadata($dummyParameter, types: [CarbonImmutable::class]);
            $computedMetadata = makeClassMetadata([
                'firstName' => makePropertyMetadata($dummyParameter, name: 'firstName'),
                'summary' => makePropertyMetadata(
                    $dummyParameter,
                    name: 'summary',
                    isComputed: false,
                    computer: ComputedKeysComputer::class,
                ),
                'missing' => makePropertyMetadata($dummyParameter, name: 'missing'),
            ]);

            // Act
            $computedFallback = invokeAbstractDataMethod(SongData::class, 'resolveComputedValue', [
                makeCreationContext($computedMetadata),
                makePropertyMetadata(
                    $dummyParameter,
                    name: 'summary',
                    isComputed: false,
                    computer: ComputedKeysComputer::class,
                ),
                ['firstName' => 'Brian'],
            ]);
            $resolvedFallback = invokeAbstractDataMethod(SongData::class, 'resolveComputedValue', [
                makeCreationContext(makeClassMetadata([])),
                makePropertyMetadata($dummyParameter, hasDefaultValue: true, defaultValue: 'fallback'),
                [],
            ]);
            $resolvedNull = invokeAbstractDataMethod(SongData::class, 'resolveComputedValue', [
                makeCreationContext(makeClassMetadata([])),
                makePropertyMetadata($dummyParameter),
                [],
            ]);
            $hydratedOptional = invokeAbstractDataMethod(SongData::class, 'hydrateValue', [$mixedProperty, new Optional()]);
            $hydratedCast = invokeAbstractDataMethod(SongData::class, 'hydrateValue', [$castProperty, '7']);
            $hydratedDataList = invokeAbstractDataMethod(SongData::class, 'hydrateValue', [$listTypeProperty, [1, '2']]);
            $hydratedDataListCasted = invokeAbstractDataMethod(SongData::class, 'hydrateValue', [$listCastProperty, ['1', 2]]);
            $hydratedCollection = invokeAbstractDataMethod(SongData::class, 'hydrateValue', [$collectionTypeProperty, [['title' => 'A', 'artist' => 'B']]]);
            $hydratedCollectionCasted = invokeAbstractDataMethod(SongData::class, 'hydrateValue', [$collectionCastProperty, ['1']]);
            $hydratedObject = invokeAbstractDataMethod(SongData::class, 'hydrateValue', [$mixedProperty, new stdClass()]);
            $resolvedValue = invokeAbstractDataMethod(SongData::class, 'resolveValue', [
                makeClassMetadata([]),
                makePropertyMetadata($dummyParameter, name: 'title', inputName: 'song_title', types: ['string']),
                ['title' => 'Fallback Name'],
            ]);
            $resolvedInputValue = invokeAbstractDataMethod(SongData::class, 'resolveInputValue', [
                makePropertyMetadata($dummyParameter, name: 'title', inputName: 'song_title', types: ['string']),
                ['song_title' => 'Mapped', 'title' => 'Fallback Name'],
            ]);
            $resolvedInputValueSameName = invokeAbstractDataMethod(SongData::class, 'resolveInputValue', [
                makePropertyMetadata($dummyParameter, name: 'title', inputName: 'title', types: ['string']),
                ['title' => 'Exact Match'],
            ]);
            $hydratedBool = invokeAbstractDataMethod(SongData::class, 'hydrateType', ['bool', 1]);
            $hydratedFloatNull = invokeAbstractDataMethod(SongData::class, 'hydrateType', ['float', null]);
            $hydratedFloatArray = invokeAbstractDataMethod(SongData::class, 'hydrateType', ['float', []]);
            $hydratedIntArray = invokeAbstractDataMethod(SongData::class, 'hydrateType', ['int', []]);
            $hydratedUnknown = invokeAbstractDataMethod(SongData::class, 'hydrateType', ['unknown-type', 'value']);
            $hydratedEnum = invokeAbstractDataMethod(SongData::class, 'hydrateType', [UserStatus::class, UserStatus::Active]);
            $hydratedEnumArray = invokeAbstractDataMethod(SongData::class, 'hydrateType', [UserStatus::class, []]);
            $hydratedMoodArray = invokeAbstractDataMethod(SongData::class, 'hydrateType', [Mood::class, []]);
            $hydratedMoodEnum = invokeAbstractDataMethod(SongData::class, 'hydrateType', [Mood::class, 'Happy']);
            $hydratedDto = invokeAbstractDataMethod(SongData::class, 'hydrateType', [SongData::class, SongData::create([
                'title' => 'A',
                'artist' => 'B',
            ])]);
            $hydratedDtoPrimitive = invokeAbstractDataMethod(SongData::class, 'hydrateType', [SongData::class, 'nope']);
            $hydratedDateNumber = invokeAbstractDataMethod(SongData::class, 'hydrateType', [CarbonImmutable::class, 123]);
            $hydratedDateString = invokeAbstractDataMethod(SongData::class, 'hydrateType', [CarbonImmutable::class, $this->timestamp]);
            $serializedNull = invokeAbstractDataMethod(SongData::class, 'serializeAny', [
                new Optional(),
                serializationContext(),
                $mixedProperty,
            ]);
            $serializedMood = invokeAbstractDataMethod(SongData::class, 'serializeAny', [
                Mood::Sad,
                serializationContext(),
                $unitEnumProperty,
            ]);
            $serializedDate = invokeAbstractDataMethod(SongData::class, 'serializeAny', [
                CarbonImmutable::parse($this->timestamp),
                serializationContext(),
                $dateProperty,
            ]);
            $serializedDataList = invokeAbstractDataMethod(SongData::class, 'serializeAny', [
                new DataList([1, 2]),
                serializationContext(),
                $listTypeProperty,
            ]);
            $serializedCollection = invokeAbstractDataMethod(SongData::class, 'serializeAny', [
                new DataCollection([
                    SongData::create(['title' => 'A', 'artist' => 'B']),
                ]),
                serializationContext(),
                $collectionTypeProperty,
            ]);
            $serializedObject = invokeAbstractDataMethod(SongData::class, 'serializeAny', [
                (object) ['name' => 'Brian'],
                serializationContext(),
                $mixedProperty,
            ]);
            $hydratedCollectionFromString = invokeAbstractDataMethod(SongData::class, 'hydrateCollectionItems', [$collectionTypeProperty, 'nope', false]);
            $hydratedCollectionFromData = invokeAbstractDataMethod(SongData::class, 'hydrateCollectionItems', [$collectionTypeProperty, new DataCollection([
                ['title' => 'A', 'artist' => 'B'],
            ]), false]);
            $hydratedCollectionItemDataList = invokeAbstractDataMethod(SongData::class, 'hydrateCollectionItem', [
                makePropertyMetadata($dummyParameter, dataListType: 'null'),
                'value',
            ]);
            $hydratedCollectionItemOptional = invokeAbstractDataMethod(SongData::class, 'hydrateCollectionItem', [
                makePropertyMetadata($dummyParameter, dataListType: Optional::class),
                'value',
            ]);
            $hydratedCollectionItem = invokeAbstractDataMethod(SongData::class, 'hydrateCollectionItem', [$mixedProperty, 'value']);
            $serializedCollectionItemCast = invokeAbstractDataMethod(SongData::class, 'serializeCollectionItem', [
                makePropertyMetadata($dummyParameter, dataListCastClass: IntegerStringCast::class),
                7,
                serializationContext(),
            ]);
            $serializedCollectionItem = invokeAbstractDataMethod(SongData::class, 'serializeCollectionItem', [
                $mixedProperty,
                'value',
                serializationContext(),
            ]);
            $collectionItemProperty = invokeAbstractDataMethod(SongData::class, 'collectionItemProperty', [
                $collectionTypeProperty,
            ]);
            $sameCollectionItemProperty = invokeAbstractDataMethod(SongData::class, 'collectionItemProperty', [
                $collectionTypeProperty,
            ]);
            $serializedArrayEnum = invokeAbstractDataMethod(SongData::class, 'serializeAny', [
                [UserStatus::Active],
                serializationContext(),
            ]);
            $serializedPlainArray = invokeAbstractDataMethod(SongData::class, 'serializeAny', [
                [
                    'meta' => ['published' => true, 'plays' => 7],
                    'notes' => null,
                ],
                serializationContext(),
            ]);
            $serializedDtoArray = invokeAbstractDataMethod(SongData::class, 'serializeAny', [
                [
                    SongData::create(['title' => 'A', 'artist' => 'B']),
                    SongData::create(['title' => 'C', 'artist' => 'D']),
                ],
                serializationContext(),
            ]);
            $plainArrayFastPath = invokeAbstractDataMethod(SongData::class, 'isPlainArrayValue', [[
                'meta' => ['published' => true, 'plays' => 7],
                'notes' => null,
            ]]);
            $classifiedPlainArray = invokeAbstractDataMethod(SongData::class, 'classifyArrayValue', [[
                'meta' => ['published' => true, 'plays' => 7],
                'notes' => null,
            ]]);
            $classifiedDtoArray = invokeAbstractDataMethod(SongData::class, 'classifyArrayValue', [[
                SongData::create(['title' => 'A', 'artist' => 'B']),
                SongData::create(['title' => 'C', 'artist' => 'D']),
            ]]);
            $dataObjectArrayFastPath = invokeAbstractDataMethod(SongData::class, 'containsOnlyDataObjects', [[
                SongData::create(['title' => 'A', 'artist' => 'B']),
                SongData::create(['title' => 'C', 'artist' => 'D']),
            ]]);

            // Assert
            expect($computedFallback)->toBe('firstName')
                ->and($resolvedFallback)->toBe('fallback')
                ->and($resolvedNull)->toBeNull()
                ->and($hydratedOptional)->toBeInstanceOf(Optional::class)
                ->and($hydratedCast)->toBe(7)
                ->and($hydratedDataList)->toBeInstanceOf(DataList::class)
                ->and($hydratedDataListCasted)->toBeInstanceOf(DataList::class)
                ->and($hydratedCollection)->toBeInstanceOf(DataCollection::class)
                ->and($hydratedCollectionCasted)->toBeInstanceOf(DataCollection::class)
                ->and($hydratedObject)->toBeInstanceOf(stdClass::class)
                ->and($resolvedValue)->toBe('Fallback Name')
                ->and($resolvedInputValue)->toBe([true, 'Mapped'])
                ->and($resolvedInputValueSameName)->toBe([true, 'Exact Match'])
                ->and($hydratedBool)->toBeTrue()
                ->and($hydratedFloatNull)->toBeNull()
                ->and($hydratedFloatArray)->toBe([])
                ->and($hydratedIntArray)->toBe([])
                ->and($hydratedUnknown)->toBe('value')
                ->and($hydratedEnum)->toBe(UserStatus::Active)
                ->and($hydratedEnumArray)->toBe([])
                ->and($hydratedMoodArray)->toBe([])
                ->and($hydratedMoodEnum)->toBe(Mood::Happy)
                ->and($hydratedDto)->toBeInstanceOf(SongData::class)
                ->and($hydratedDtoPrimitive)->toBe('nope')
                ->and($hydratedDateNumber)->toBe(123)
                ->and($hydratedDateString)->toBeInstanceOf(CarbonImmutable::class)
                ->and($serializedNull)->toBeNull()
                ->and($serializedMood)->toBe('Sad')
                ->and($serializedDate)->toBe($this->timestamp)
                ->and($serializedDataList)->toBe([1, 2])
                ->and($serializedCollection)->toBe([
                    ['title' => 'A', 'artist' => 'B'],
                ])
                ->and($serializedObject)->toBe([
                    'name' => 'Brian',
                ])
                ->and($hydratedCollectionFromString)->toBe([])
                ->and($hydratedCollectionFromData[0])->toBeInstanceOf(SongData::class)
                ->and($hydratedCollectionItemDataList)->toBe('value')
                ->and($hydratedCollectionItemOptional)->toBe('value')
                ->and($hydratedCollectionItem)->toBe('value')
                ->and($serializedCollectionItemCast)->toBe('7')
                ->and($serializedCollectionItem)->toBe('value')
                ->and($collectionItemProperty)->toEqual($sameCollectionItemProperty)
                ->and($serializedArrayEnum)->toBe(['active'])
                ->and($serializedPlainArray)->toBe([
                    'meta' => ['published' => true, 'plays' => 7],
                    'notes' => null,
                ])
                ->and($serializedDtoArray)->toBe([
                    ['title' => 'A', 'artist' => 'B'],
                    ['title' => 'C', 'artist' => 'D'],
                ])
                ->and($plainArrayFastPath)->toBeTrue()
                ->and($classifiedPlainArray)->toBe([
                    'kind' => 'plain',
                    'plain' => [
                        'meta' => ['published' => true, 'plays' => 7],
                        'notes' => null,
                    ],
                ])
                ->and($classifiedDtoArray)->toBe([
                    'kind' => 'data',
                    'plain' => null,
                ])
                ->and($dataObjectArrayFastPath)->toBeTrue();
        });
    });
});

function invokeAbstractDataMethod(string $class, string $method, array $arguments = []): mixed
{
    $reflection = new ReflectionMethod($class, $method);

    return $reflection->invokeArgs(null, $arguments);
}

function invokeAbstractDataInstanceMethod(object $object, string $method, array $arguments = []): mixed
{
    $reflection = new ReflectionMethod($object, $method);

    return $reflection->invokeArgs($object, $arguments);
}

function makePropertyMetadata(
    ReflectionParameter $parameter,
    ?string $name = null,
    ?string $inputName = null,
    ?string $outputName = null,
    array $types = ['mixed'],
    bool $nullable = false,
    bool $hasDefaultValue = false,
    mixed $defaultValue = null,
    bool $replaceEmptyStringsWithNull = false,
    bool $inferValidationRules = false,
    bool $isOptional = false,
    bool $isSensitive = false,
    bool $isComputed = false,
    ?string $computer = null,
    ?string $castClass = null,
    ?string $dataListType = null,
    ?string $dataListCastClass = null,
    ?string $dataCollectionType = null,
    ?string $dataCollectionCastClass = null,
): PropertyMetadata {
    $name ??= $parameter->getName();
    $inputName ??= $name;
    $outputName ??= $name;
    $hasCollectionItemCast = ($dataListCastClass !== null && is_subclass_of($dataListCastClass, CastInterface::class))
        || ($dataCollectionCastClass !== null && is_subclass_of($dataCollectionCastClass, CastInterface::class));

    return new PropertyMetadata(
        name: $name,
        inputName: $inputName,
        outputName: $outputName,
        types: $types,
        typeKinds: PropertyMetadata::classifyTypes($types),
        nullable: $nullable,
        hasDefaultValue: $hasDefaultValue,
        defaultValue: $defaultValue,
        replaceEmptyStringsWithNull: $replaceEmptyStringsWithNull,
        inferValidationRules: $inferValidationRules,
        isOptional: $isOptional,
        isSensitive: $isSensitive,
        isComputed: $isComputed,
        isLazy: false,
        computer: $computer,
        lazyResolver: null,
        lazyGroups: [],
        includeConditions: [],
        excludeConditions: [],
        castClass: $castClass,
        cast: $castClass !== null ? new $castClass() : null,
        dataListType: $dataListType,
        dataListCastClass: $dataListCastClass,
        dataListCast: $dataListCastClass !== null ? new $dataListCastClass() : null,
        dataCollectionType: $dataCollectionType,
        dataListTypeKind: PropertyMetadata::nullableTypeKind($dataListType),
        dataCollectionCastClass: $dataCollectionCastClass,
        dataCollectionCast: $dataCollectionCastClass !== null ? new $dataCollectionCastClass() : null,
        dataCollectionTypeKind: PropertyMetadata::nullableTypeKind($dataCollectionType),
        hasCollectionItemCast: $hasCollectionItemCast,
        validationRules: [],
        itemValidationRules: [],
        parameter: $parameter,
        property: null,
    );
}

function makeClassMetadata(array $properties, bool $forbidUndefinedValues = false, bool $forbidSuperfluousKeys = false): ClassMetadata
{
    return new ClassMetadata(
        class: SongData::class,
        reflection: new ReflectionClass(SongData::class),
        properties: $properties,
        forbidUndefinedValues: $forbidUndefinedValues,
        forbidSuperfluousKeys: $forbidSuperfluousKeys,
        inferValidationRules: false,
        validatorMutator: null,
        requestPayloadResolver: null,
        modelPayloadResolver: null,
        stringifier: null,
        factory: null,
    );
}

function makeCreationContext(ClassMetadata $metadata): CreationContext
{
    return new CreationContext($metadata);
}

function serializationContext(): SerializationContext
{
    return new SerializationContext(
        new RecursionGuard(),
        new SerializationOptions(),
    );
}
