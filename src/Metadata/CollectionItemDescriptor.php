<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Metadata;

use Cline\Struct\Contracts\CastInterface;
use ReflectionParameter;
use ReflectionProperty;

/**
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 * @internal
 */
final readonly class CollectionItemDescriptor
{
    /**
     * @param list<string> $types
     * @param list<string> $typeKinds
     */
    public function __construct(
        public array $types,
        public array $typeKinds,
        /** @var null|class-string<CastInterface> */
        public ?string $castClass,
        public ?CastInterface $cast,
    ) {}

    public function toProperty(
        string $name,
        string $inputName,
        string $outputName,
        ReflectionParameter $parameter,
        ?ReflectionProperty $property,
    ): PropertyMetadata {
        return new PropertyMetadata(
            name: $name,
            inputName: $inputName,
            outputName: $outputName,
            types: $this->types,
            typeKinds: $this->typeKinds,
            nullable: false,
            hasDefaultValue: false,
            defaultValue: null,
            replaceEmptyStringsWithNull: false,
            inferValidationRules: false,
            isOptional: false,
            isSensitive: false,
            isEncrypted: false,
            isComputed: false,
            hasCollectionResultAttribute: false,
            hasCollectionSourceAttribute: false,
            isLazy: false,
            computer: null,
            lazyResolver: null,
            lazyGroups: [],
            includeConditions: [],
            excludeConditions: [],
            castClass: $this->castClass,
            cast: $this->cast,
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
            property: $property,
        );
    }
}
