<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Metadata\ClassMetadata;
use Cline\Struct\Metadata\PropertyMetadata;
use Tests\Fixtures\Data\MappedUserData;

describe('ClassMetadata', function (): void {
    test('builds an input name lookup for fast membership checks', function (): void {
        // Arrange
        $reflection = new ReflectionClass(MappedUserData::class);
        $parameter = $reflection->getConstructor()->getParameters()[0];

        $metadata = new ClassMetadata(
            class: MappedUserData::class,
            reflection: $reflection,
            properties: [
                'id' => new PropertyMetadata(
                    name: 'id',
                    inputName: 'identifier',
                    outputName: 'id',
                    types: ['int'],
                    typeKinds: ['int'],
                    nullable: false,
                    hasDefaultValue: false,
                    defaultValue: null,
                    replaceEmptyStringsWithNull: false,
                    inferValidationRules: false,
                    isOptional: false,
                    isSensitive: false,
                    isComputed: false,
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
                ),
            ],
            forbidUndefinedValues: false,
            forbidSuperfluousKeys: true,
            inferValidationRules: false,
            validatorMutator: null,
            requestPayloadResolver: null,
            modelPayloadResolver: null,
            stringifier: null,
            factory: null,
        );

        // Assert
        expect($metadata->inputNameLookup)->toBe([
            'identifier' => true,
        ])->and($metadata->hydratedPropertyLookup)->toBe([
            'id' => true,
        ])->and($metadata->hydratedProperties)->toHaveCount(1)
            ->and($metadata->defaultProjectionProperties)->toBe([$metadata->properties['id']])
            ->and($metadata->defaultProjectionPropertiesWithoutSensitive)->toBe([$metadata->properties['id']])
            ->and($metadata->computedProperties)->toBe([])
            ->and($metadata->computedInputNamesFor('id'))->toBe([]);
    });

    test('derives computed input names from hydrated properties', function (): void {
        $reflection = new ReflectionClass(MappedUserData::class);
        $parameter = $reflection->getConstructor()->getParameters()[0];

        $metadata = new ClassMetadata(
            class: MappedUserData::class,
            reflection: $reflection,
            properties: [
                'firstName' => new PropertyMetadata(
                    name: 'firstName',
                    inputName: 'firstName',
                    outputName: 'firstName',
                    types: ['string'],
                    typeKinds: ['string'],
                    nullable: false,
                    hasDefaultValue: false,
                    defaultValue: null,
                    replaceEmptyStringsWithNull: false,
                    inferValidationRules: false,
                    isOptional: false,
                    isSensitive: false,
                    isComputed: false,
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
                ),
                'lastName' => new PropertyMetadata(
                    name: 'lastName',
                    inputName: 'lastName',
                    outputName: 'lastName',
                    types: ['string'],
                    typeKinds: ['string'],
                    nullable: false,
                    hasDefaultValue: false,
                    defaultValue: null,
                    replaceEmptyStringsWithNull: false,
                    inferValidationRules: false,
                    isOptional: false,
                    isSensitive: false,
                    isComputed: false,
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
                ),
                'fullName' => new PropertyMetadata(
                    name: 'fullName',
                    inputName: 'fullName',
                    outputName: 'fullName',
                    types: ['string'],
                    typeKinds: ['string'],
                    nullable: false,
                    hasDefaultValue: false,
                    defaultValue: null,
                    replaceEmptyStringsWithNull: false,
                    inferValidationRules: false,
                    isOptional: false,
                    isSensitive: false,
                    isComputed: true,
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
                ),
            ],
            forbidUndefinedValues: false,
            forbidSuperfluousKeys: true,
            inferValidationRules: false,
            validatorMutator: null,
            requestPayloadResolver: null,
            modelPayloadResolver: null,
            stringifier: null,
            factory: null,
        );

        expect($metadata->computedInputNamesFor('fullName'))->toBe(['firstName', 'lastName'])
            ->and($metadata->computedInputNamesFor('firstName'))->toBe(['lastName'])
            ->and($metadata->computedInputNamesFor('missing'))->toBe([])
            ->and($metadata->computedInputNames)->toBe([
                'fullName' => ['firstName', 'lastName'],
            ]);
    });
});
