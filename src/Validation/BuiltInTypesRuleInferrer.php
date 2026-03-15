<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Validation;

use Cline\Struct\AbstractData;
use Cline\Struct\Contracts\InfersValidationRules;
use Cline\Struct\Metadata\ClassMetadata;
use Cline\Struct\Metadata\PropertyMetadata;
use Cline\Struct\Support\DataCollection;
use Cline\Struct\Support\DataList;
use Cline\Struct\Support\LazyDataCollection;
use Cline\Struct\Support\LazyDataList;
use Cline\Struct\Support\Optional;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Illuminate\Validation\Rule;

use function enum_exists;
use function in_array;
use function is_a;

/**
 * Infers Laravel rules from property and item types.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class BuiltInTypesRuleInferrer implements InfersValidationRules
{
    public function handle(ClassMetadata $metadata, PropertyMetadata $property): array
    {
        if (!$property->inferValidationRules) {
            return [];
        }

        if (
            $property->dataListType !== null
            || $property->dataListCastClass !== null
            || $property->lazyDataListType !== null
            || $property->lazyDataListCastClass !== null
            || $this->propertyHasType($property, DataList::class)
            || $this->propertyHasType($property, LazyDataList::class)
        ) {
            return ['array', 'list'];
        }

        if (
            $property->dataCollectionType !== null
            || $property->dataCollectionCastClass !== null
            || $property->lazyDataCollectionType !== null
            || $property->lazyDataCollectionCastClass !== null
            || $this->propertyHasType($property, DataCollection::class)
            || $this->propertyHasType($property, LazyDataCollection::class)
        ) {
            return ['array'];
        }

        if ($property->laravelCollectionType !== null || $property->laravelCollectionCastClass !== null || $this->propertyHasType($property, Collection::class)) {
            return ['array'];
        }

        if ($property->lazyLaravelCollectionType !== null || $property->lazyLaravelCollectionCastClass !== null || $this->propertyHasType($property, LazyCollection::class)) {
            return ['array'];
        }

        return $this->rulesForType($this->primaryType($property));
    }

    public function handleItems(ClassMetadata $metadata, PropertyMetadata $property): array
    {
        if (!$property->inferValidationRules) {
            return [];
        }

        if (
            $property->dataListCastClass !== null
            || $property->dataCollectionCastClass !== null
            || $property->lazyDataListCastClass !== null
            || $property->lazyDataCollectionCastClass !== null
            || $property->laravelCollectionCastClass !== null
            || $property->lazyLaravelCollectionCastClass !== null
        ) {
            return [];
        }

        $type = $property->dataListType
            ?? $property->dataCollectionType
            ?? $property->lazyDataListType
            ?? $property->lazyDataCollectionType
            ?? $property->laravelCollectionType
            ?? $property->lazyLaravelCollectionType;

        if ($type === null) {
            return [];
        }

        return $this->rulesForType($type);
    }

    /**
     * @return array<int, mixed>
     */
    private function rulesForType(?string $type): array
    {
        if ($type === null || $type === 'mixed') {
            return [];
        }

        return match (true) {
            $type === 'array' => ['array'],
            $type === 'bool' => ['boolean'],
            $type === 'float' => ['numeric'],
            $type === 'int' => ['integer'],
            $type === 'string' => ['string'],
            enum_exists($type) => [Rule::enum($type)],
            $type === DateTimeInterface::class,
            is_a($type, DateTimeInterface::class, true) => ['date'],
            is_a($type, AbstractData::class, true) => ['array'],
            default => [],
        };
    }

    private function primaryType(PropertyMetadata $property): ?string
    {
        foreach ($property->types as $type) {
            if ($type === 'null') {
                continue;
            }

            if ($type === Optional::class) {
                continue;
            }

            return $type;
        }

        return null;
    }

    /**
     * @param class-string $expected
     */
    private function propertyHasType(PropertyMetadata $property, string $expected): bool
    {
        return in_array($expected, $property->types, true);
    }
}
