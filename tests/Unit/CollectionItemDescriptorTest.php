<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Metadata\MetadataFactory;
use Cline\Struct\Metadata\PropertyMetadata;
use Tests\Fixtures\Casts\IntegerStringCast;
use Tests\Fixtures\Data\InstrumentedCastedListData;
use Tests\Fixtures\Data\MappedUserData;

describe('Collection item descriptors', function (): void {
    test('derives collection item metadata without cloning a property', function (): void {
        $reflection = new ReflectionClass(MappedUserData::class);
        $parameter = $reflection->getConstructor()->getParameters()[0];
        $cast = new IntegerStringCast();
        $property = new PropertyMetadata(
            name: 'numbers',
            inputName: 'numbers',
            outputName: 'numbers',
            types: ['array'],
            typeKinds: ['array'],
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
            dataListType: 'int',
            dataListCastClass: IntegerStringCast::class,
            dataListCast: $cast,
            dataCollectionType: null,
            dataListTypeKind: 'int',
            dataCollectionCastClass: null,
            dataCollectionCast: null,
            dataCollectionTypeKind: null,
            hasCollectionItemCast: true,
            validationRules: [],
            itemValidationRules: [],
            parameter: $parameter,
            property: null,
        );

        expect($property->collectionItemTypes())->toBe(['int'])
            ->and($property->collectionItemTypeKinds())->toBe(['int'])
            ->and($property->collectionItemCast())->toBe($cast)
            ->and($property->forCollectionItem()->types)->toBe(['int'])
            ->and($property->forCollectionItem()->cast)->toBe($cast);
    });

    test('keeps collection item metadata lazy in class metadata', function (): void {
        $property = resolve(MetadataFactory::class)
            ->for(InstrumentedCastedListData::class)
            ->properties['numbers'];

        expect($property->collectionItemDescriptor())->not->toBeNull()
            ->and($property->toCachePayload()['collectionItemDescriptor'])->toBe([
                'types' => ['mixed'],
                'typeKinds' => ['mixed'],
                'castClass' => IntegerStringCast::class,
            ])
            ->and($property->forCollectionItem()->types)->toBe(['mixed'])
            ->and($property->forCollectionItem()->cast?->get(
                $property->forCollectionItem(),
                '1',
            ))->toBe(1);
    });
});
