<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Carbon\CarbonImmutable;
use Cline\Struct\Casts\CarbonCast;
use Cline\Struct\Casts\CarbonImmutableCast;
use Cline\Struct\Casts\CarbonInterfaceCast;
use Cline\Struct\Casts\DateTimeInterfaceCast;
use Cline\Struct\Metadata\PropertyMetadata;
use Tests\Fixtures\Data\MappedUserData;

describe('Built-in date casts', function (): void {
    test('captures date configuration once per cast instance', function (string $castClass): void {
        config([
            'struct.date_format' => 'Y-m-d H:i:s',
            'struct.date_timezone' => 'America/New_York',
        ]);

        $cast = new $castClass();
        $property = makeDateCastProperty();

        config([
            'struct.date_format' => \DATE_ATOM,
            'struct.date_timezone' => 'UTC',
        ]);

        $serialized = $cast->set(
            $property,
            CarbonImmutable::create(2_024, 5, 16, 16, 0, 0, 'UTC'),
        );
        $parsed = $cast->get($property, '2024-05-16 12:00:00');

        expect($serialized)->toBe('2024-05-16 12:00:00')
            ->and($parsed->timezone->getName())->toBe('America/New_York');
    })->with([
        CarbonImmutableCast::class,
        CarbonCast::class,
        CarbonInterfaceCast::class,
        DateTimeInterfaceCast::class,
    ]);
});

function makeDateCastProperty(): PropertyMetadata
{
    $reflection = new ReflectionClass(MappedUserData::class);
    $parameter = $reflection->getConstructor()->getParameters()[0];

    return new PropertyMetadata(
        name: 'publishedAt',
        inputName: 'publishedAt',
        outputName: 'publishedAt',
        types: [CarbonImmutable::class],
        typeKinds: ['datetime'],
        nullable: false,
        hasDefaultValue: false,
        defaultValue: null,
        replaceEmptyStringsWithNull: false,
        inferValidationRules: false,
        isOptional: false,
        isSensitive: false,
        isEncrypted: false,
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
    );
}
