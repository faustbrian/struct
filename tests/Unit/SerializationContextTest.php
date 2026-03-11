<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Metadata\ClassMetadata;
use Cline\Struct\Metadata\MetadataFactory;
use Cline\Struct\Metadata\PropertyMetadata;
use Cline\Struct\Serialization\ProjectionPlan;
use Cline\Struct\Serialization\SerializationContext;
use Cline\Struct\Serialization\SerializationOptions;
use Cline\Struct\Support\RecursionGuard;
use Tests\Fixtures\Data\MappedUserData;
use Tests\Fixtures\Data\MultiComputedData;

describe('SerializationContext', function (): void {
    test('reuses derived child contexts within one serialization pass', function (): void {
        $context = new SerializationContext(
            new RecursionGuard(),
            new SerializationOptions(
                include: ['posts.author.profile.bio'],
            ),
        );

        expect($context->child('posts'))->toBe($context->child('posts'));
    });

    test('caches scoped projection plans per class', function (): void {
        $context = new SerializationContext(
            new RecursionGuard(),
            new SerializationOptions(
                include: ['full_name'],
                exclude: ['age'],
            ),
        );
        $metadata = resolve(MetadataFactory::class)
            ->for(MappedUserData::class);
        $property = new ReflectionProperty($context, 'projectionPlans');

        expect($property->getValue($context))->toBe([]);

        $first = $context->projectionPlanFor($metadata);
        $second = $context->projectionPlanFor($metadata);
        $cache = $property->getValue($context);

        expect($first)->toBeInstanceOf(ProjectionPlan::class)
            ->and($first)->toBe($second)
            ->and($cache)->toHaveCount(1)
            ->and($cache[MappedUserData::class])
            ->toBe($first);
    });

    test('caches resolved metadata within one serialization pass', function (): void {
        $context = new SerializationContext(
            new RecursionGuard(),
            new SerializationOptions(),
        );
        $metadata = new ClassMetadata(
            class: MultiComputedData::class,
            reflection: new ReflectionClass(MultiComputedData::class),
            properties: [],
            forbidUndefinedValues: false,
            forbidSuperfluousKeys: false,
            inferValidationRules: false,
            validatorMutator: null,
            requestPayloadResolver: null,
            modelPayloadResolver: null,
            stringifier: null,
            factory: null,
        );
        $calls = 0;

        $resolved = $context->metadataFor(MultiComputedData::class, function () use (&$calls, $metadata): ClassMetadata {
            ++$calls;

            return $metadata;
        });
        $sameResolved = $context->metadataFor(MultiComputedData::class, function () use (&$calls, $metadata): ClassMetadata {
            ++$calls;

            return $metadata;
        });

        expect($resolved)->toBe($metadata)
            ->and($sameResolved)->toBe($metadata)
            ->and($calls)->toBe(1);
    });

    test('caches computed input payloads per object for one pass', function (): void {
        $context = new SerializationContext(
            new RecursionGuard(),
            new SerializationOptions(),
        );
        $parameter = new ReflectionClass(MultiComputedData::class)
            ->getConstructor()
            ->getParameters()[0];
        $metadata = new ClassMetadata(
            class: MultiComputedData::class,
            reflection: new ReflectionClass(MultiComputedData::class),
            properties: [
                'firstName' => makeSerializationProperty($parameter, 'firstName'),
                'lastName' => makeSerializationProperty($parameter, 'lastName'),
                'fullName' => makeSerializationProperty($parameter, 'fullName', isComputed: true),
                'summary' => makeSerializationProperty($parameter, 'summary', isComputed: true),
            ],
            forbidUndefinedValues: false,
            forbidSuperfluousKeys: false,
            inferValidationRules: false,
            validatorMutator: null,
            requestPayloadResolver: null,
            modelPayloadResolver: null,
            stringifier: null,
            factory: null,
        );
        $object = new class()
        {
            public int $reads = 0;

            /** @var array<string, string> */
            private array $values = [
                'firstName' => 'Brian',
                'lastName' => 'Faust',
            ];

            public function __get(string $name): mixed
            {
                ++$this->reads;

                return $this->values[$name] ?? null;
            }
        };

        $fullNameInput = $context->computedInputFor(
            $object,
            $metadata,
        );
        $summaryInput = $context->computedInputFor(
            $object,
            $metadata,
        );
        $sameSummaryInput = $context->computedInputFor(
            $object,
            $metadata,
        );

        expect($fullNameInput)->toBe([
            'firstName' => 'Brian',
            'lastName' => 'Faust',
        ])->and($summaryInput)->toBe([
            'firstName' => 'Brian',
            'lastName' => 'Faust',
        ])->and($sameSummaryInput)->toBe([
            'firstName' => 'Brian',
            'lastName' => 'Faust',
        ])->and($object->reads)->toBe(2)
            ->and(property_exists(SerializationContext::class, 'computedPropertyInputs'))->toBeFalse();
    });

    test('extracts only hydrated inputs without whole-object snapshots', function (): void {
        $context = new SerializationContext(
            new RecursionGuard(),
            new SerializationOptions(),
        );
        $parameter = new ReflectionClass(MultiComputedData::class)
            ->getConstructor()
            ->getParameters()[0];
        $metadata = new ClassMetadata(
            class: MultiComputedData::class,
            reflection: new ReflectionClass(MultiComputedData::class),
            properties: [
                'firstName' => makeSerializationProperty($parameter, 'firstName'),
                'lastName' => makeSerializationProperty($parameter, 'lastName'),
                'fullName' => makeSerializationProperty($parameter, 'fullName', isComputed: true),
            ],
            forbidUndefinedValues: false,
            forbidSuperfluousKeys: false,
            inferValidationRules: false,
            validatorMutator: null,
            requestPayloadResolver: null,
            modelPayloadResolver: null,
            stringifier: null,
            factory: null,
        );
        $object = new class()
        {
            public string $firstName = 'Brian';

            public string $lastName = 'Faust';

            public string $unrelated = 'skip me';
        };
        $method = new ReflectionMethod($context, 'hydratedInputFor');

        expect($method->invoke($context, $object, $metadata))->toBe([
            'firstName' => 'Brian',
            'lastName' => 'Faust',
        ]);
    });

    test('reuses the base computed input map when the property is not hydrated', function (): void {
        $context = new SerializationContext(
            new RecursionGuard(),
            new SerializationOptions(),
        );
        $parameter = new ReflectionClass(MultiComputedData::class)
            ->getConstructor()
            ->getParameters()[0];
        $metadata = new ClassMetadata(
            class: MultiComputedData::class,
            reflection: new ReflectionClass(MultiComputedData::class),
            properties: [
                'firstName' => makeSerializationProperty($parameter, 'firstName'),
                'lastName' => makeSerializationProperty($parameter, 'lastName'),
                'fullName' => makeSerializationProperty($parameter, 'fullName', isComputed: true),
            ],
            forbidUndefinedValues: false,
            forbidSuperfluousKeys: false,
            inferValidationRules: false,
            validatorMutator: null,
            requestPayloadResolver: null,
            modelPayloadResolver: null,
            stringifier: null,
            factory: null,
        );
        $object = new class()
        {
            public int $reads = 0;

            /** @var array<string, string> */
            private array $values = [
                'firstName' => 'Brian',
                'lastName' => 'Faust',
            ];

            public function __get(string $name): mixed
            {
                ++$this->reads;

                return $this->values[$name] ?? null;
            }
        };

        $context->computedInputFor(
            $object,
            $metadata,
        );

        expect(property_exists(SerializationContext::class, 'computedPropertyInputs'))->toBeFalse()
            ->and($object->reads)->toBe(2);
    });

    test('excludes the current property for non-computed inputs', function (): void {
        $context = new SerializationContext(
            new RecursionGuard(),
            new SerializationOptions(),
        );
        $parameter = new ReflectionClass(MultiComputedData::class)
            ->getConstructor()
            ->getParameters()[0];
        $metadata = new ClassMetadata(
            class: MultiComputedData::class,
            reflection: new ReflectionClass(MultiComputedData::class),
            properties: [
                'firstName' => makeSerializationProperty($parameter, 'firstName'),
                'lastName' => makeSerializationProperty($parameter, 'lastName'),
                'fullName' => makeSerializationProperty($parameter, 'fullName', isComputed: true),
            ],
            forbidUndefinedValues: false,
            forbidSuperfluousKeys: false,
            inferValidationRules: false,
            validatorMutator: null,
            requestPayloadResolver: null,
            modelPayloadResolver: null,
            stringifier: null,
            factory: null,
        );
        $object = new class()
        {
            public string $firstName = 'Brian';

            public string $lastName = 'Faust';
        };

        expect($context->computedInputFor($object, $metadata, 'firstName'))->toBe([
            'lastName' => 'Faust',
        ])->and($context->computedInputFor($object, $metadata))->toBe([
            'firstName' => 'Brian',
            'lastName' => 'Faust',
        ]);
    });
});

function makeSerializationProperty(
    ReflectionParameter $parameter,
    string $name,
    bool $isComputed = false,
): PropertyMetadata {
    return new PropertyMetadata(
        name: $name,
        inputName: $name,
        outputName: $name,
        types: ['string'],
        typeKinds: ['string'],
        nullable: false,
        hasDefaultValue: false,
        defaultValue: null,
        replaceEmptyStringsWithNull: false,
        inferValidationRules: false,
        isOptional: false,
        isSensitive: false,
        isEncrypted: false,
        isComputed: $isComputed,
        isLazy: false,
        computer: null,
        lazyResolver: null,
        lazyGroups: [],
        includeConditions: [],
        excludeConditions: [],
        castClass: null,
        cast: null,
        dataListType: null,
        dataListCastClass: null,
        dataListCast: null,
        dataCollectionType: null,
        dataListTypeKind: null,
        dataCollectionCastClass: null,
        dataCollectionCast: null,
        dataCollectionTypeKind: null,
        hasCollectionItemCast: false,
        validationRules: [],
        itemValidationRules: [],
        parameter: $parameter,
        property: null,
    );
}
