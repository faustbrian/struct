<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Metadata;

use BackedEnum;
use Cline\Struct\AbstractData;
use Cline\Struct\Contracts\CastInterface;
use DateTimeInterface;
use ReflectionClass;
use ReflectionParameter;
use ReflectionProperty;

use function array_filter;
use function array_map;
use function array_values;
use function enum_exists;
use function is_array;
use function is_string;
use function is_subclass_of;

/**
 * Describes how a single data object property should be hydrated and serialized.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class PropertyMetadata
{
    /**
     * @param array<int, string>               $types
     * @param array<int, string>               $typeKinds
     * @param array<int, string>               $lazyGroups
     * @param array<int, string>               $includeConditions
     * @param array<int, string>               $excludeConditions
     * @param null|class-string<CastInterface> $castClass
     * @param null|class-string<CastInterface> $dataListCastClass
     * @param null|class-string<CastInterface> $dataCollectionCastClass
     * @param array<int, mixed>                $validationRules
     * @param array<int, mixed>                $itemValidationRules
     * @param null|class-string<CastInterface> $laravelCollectionCastClass
     * @param null|class-string<CastInterface> $lazyLaravelCollectionCastClass
     * @param null|class-string<CastInterface> $lazyDataListCastClass
     * @param null|class-string<CastInterface> $lazyDataCollectionCastClass
     */
    public function __construct(
        public string $name,
        public string $inputName,
        public string $outputName,
        public array $types,
        public array $typeKinds,
        public bool $nullable,
        public bool $hasDefaultValue,
        public mixed $defaultValue,
        public bool $replaceEmptyStringsWithNull,
        public bool $inferValidationRules,
        public bool $isOptional,
        public bool $isSensitive,
        public bool $isEncrypted,
        public bool $isComputed,
        public bool $hasCollectionResultAttribute,
        public bool $hasCollectionSourceAttribute,
        public bool $isLazy,
        public ?string $computer,
        public ?string $lazyResolver,
        public array $lazyGroups,
        public array $includeConditions,
        public array $excludeConditions,
        public ?string $castClass,
        public ?CastInterface $cast,
        public ?string $dataListType,
        public ?string $dataListCastClass,
        public ?CastInterface $dataListCast,
        public ?string $dataCollectionType,
        public ?string $dataListTypeKind,
        public ?string $dataCollectionCastClass,
        public ?CastInterface $dataCollectionCast,
        public ?string $dataCollectionTypeKind,
        public bool $hasCollectionItemCast,
        public array $validationRules,
        public array $itemValidationRules,
        public ReflectionParameter $parameter,
        public ?ReflectionProperty $property,
        public ?CollectionItemDescriptor $collectionItemDescriptor = null,
        public ?string $laravelCollectionType = null,
        public ?string $laravelCollectionCastClass = null,
        public ?CastInterface $laravelCollectionCast = null,
        public ?string $laravelCollectionTypeKind = null,
        public ?string $lazyLaravelCollectionType = null,
        public ?string $lazyLaravelCollectionCastClass = null,
        public ?CastInterface $lazyLaravelCollectionCast = null,
        public ?string $lazyLaravelCollectionTypeKind = null,
        public ?string $lazyDataListType = null,
        public ?string $lazyDataListCastClass = null,
        public ?CastInterface $lazyDataListCast = null,
        public ?string $lazyDataListTypeKind = null,
        public ?string $lazyDataCollectionType = null,
        public ?string $lazyDataCollectionCastClass = null,
        public ?CastInterface $lazyDataCollectionCast = null,
        public ?string $lazyDataCollectionTypeKind = null,
        public bool $hasGeneratedValueAttribute = false,
        public bool $hasCollectionTransformAttribute = false,
        public bool $hasLaravelCollectionTransformAttribute = false,
        public bool $hasLazyLaravelCollectionTransformAttribute = false,
        public bool $requiresHydrationContext = false,
    ) {}

    /**
     * @param class-string            $class
     * @param ReflectionClass<object> $reflection
     * @param array<string, mixed>    $payload
     */
    public static function fromCachePayload(
        string $class,
        ReflectionClass $reflection,
        ReflectionParameter $parameter,
        array $payload,
    ): self {
        $propertyName = $payload['propertyName'] ?? null;
        $property = is_string($propertyName) && $reflection->hasProperty($propertyName)
            ? $reflection->getProperty($propertyName)
            : null;
        $name = $payload['name'] ?? null;
        $inputName = $payload['inputName'] ?? null;
        $outputName = $payload['outputName'] ?? null;
        $types = self::stringList($payload['types'] ?? null, ['mixed']);
        $castClass = self::castClass($payload['castClass'] ?? null);
        $dataListType = is_string($payload['dataListType'] ?? null) ? $payload['dataListType'] : null;
        $dataListCastClass = self::castClass($payload['dataListCastClass'] ?? null);
        $dataCollectionType = is_string($payload['dataCollectionType'] ?? null) ? $payload['dataCollectionType'] : null;
        $dataCollectionCastClass = self::castClass($payload['dataCollectionCastClass'] ?? null);
        $lazyDataListType = is_string($payload['lazyDataListType'] ?? null) ? $payload['lazyDataListType'] : null;
        $lazyDataListCastClass = self::castClass($payload['lazyDataListCastClass'] ?? null);
        $lazyDataCollectionType = is_string($payload['lazyDataCollectionType'] ?? null) ? $payload['lazyDataCollectionType'] : null;
        $lazyDataCollectionCastClass = self::castClass($payload['lazyDataCollectionCastClass'] ?? null);
        $laravelCollectionType = is_string($payload['laravelCollectionType'] ?? null) ? $payload['laravelCollectionType'] : null;
        $laravelCollectionCastClass = self::castClass($payload['laravelCollectionCastClass'] ?? null);
        $lazyLaravelCollectionType = is_string($payload['lazyLaravelCollectionType'] ?? null) ? $payload['lazyLaravelCollectionType'] : null;
        $lazyLaravelCollectionCastClass = self::castClass($payload['lazyLaravelCollectionCastClass'] ?? null);
        $dataListCast = self::deferredCast($dataListCastClass);
        $dataCollectionCast = self::deferredCast($dataCollectionCastClass);
        $lazyDataListCast = self::deferredCast($lazyDataListCastClass);
        $lazyDataCollectionCast = self::deferredCast($lazyDataCollectionCastClass);
        $laravelCollectionCast = self::deferredCast($laravelCollectionCastClass);
        $lazyLaravelCollectionCast = self::deferredCast($lazyLaravelCollectionCastClass);
        $hasCollectionItemCast = (bool) ($payload['hasCollectionItemCast'] ?? null);
        $collectionItemDescriptor = self::collectionItemDescriptorFromPayload(
            $payload,
            $dataListType,
            $dataCollectionType,
            $lazyDataListType,
            $lazyDataCollectionType,
            $laravelCollectionType,
            $lazyLaravelCollectionType,
            $dataListCastClass,
            $dataCollectionCastClass,
            $lazyLaravelCollectionCastClass,
            $lazyDataListCastClass,
            $lazyDataCollectionCastClass,
            $laravelCollectionCastClass,
            $dataListCast,
            $dataCollectionCast,
            $lazyLaravelCollectionCast,
            $lazyDataListCast,
            $lazyDataCollectionCast,
            $laravelCollectionCast,
        );

        return new self(
            name: is_string($name) ? $name : $parameter->getName(),
            inputName: is_string($inputName) ? $inputName : $parameter->getName(),
            outputName: is_string($outputName) ? $outputName : $parameter->getName(),
            types: $types,
            typeKinds: self::typeKindList($payload['typeKinds'] ?? null, $types),
            nullable: (bool) ($payload['nullable'] ?? false),
            hasDefaultValue: (bool) ($payload['hasDefaultValue'] ?? false),
            defaultValue: $payload['defaultValue'] ?? null,
            replaceEmptyStringsWithNull: (bool) ($payload['replaceEmptyStringsWithNull'] ?? false),
            inferValidationRules: (bool) ($payload['inferValidationRules'] ?? false),
            isOptional: (bool) ($payload['isOptional'] ?? false),
            isSensitive: (bool) ($payload['isSensitive'] ?? false),
            isEncrypted: (bool) ($payload['isEncrypted'] ?? false),
            isComputed: (bool) ($payload['isComputed'] ?? false),
            hasGeneratedValueAttribute: (bool) ($payload['hasGeneratedValueAttribute'] ?? false),
            hasCollectionResultAttribute: (bool) ($payload['hasCollectionResultAttribute'] ?? false),
            hasCollectionSourceAttribute: (bool) ($payload['hasCollectionSourceAttribute'] ?? false),
            hasCollectionTransformAttribute: (bool) ($payload['hasCollectionTransformAttribute'] ?? false),
            hasLaravelCollectionTransformAttribute: (bool) ($payload['hasLaravelCollectionTransformAttribute'] ?? false),
            hasLazyLaravelCollectionTransformAttribute: (bool) ($payload['hasLazyLaravelCollectionTransformAttribute'] ?? false),
            isLazy: (bool) ($payload['isLazy'] ?? false),
            computer: is_string($payload['computer'] ?? null) ? $payload['computer'] : null,
            lazyResolver: is_string($payload['lazyResolver'] ?? null) ? $payload['lazyResolver'] : null,
            lazyGroups: self::stringList($payload['lazyGroups'] ?? null),
            includeConditions: self::stringList($payload['includeConditions'] ?? null),
            excludeConditions: self::stringList($payload['excludeConditions'] ?? null),
            castClass: $castClass,
            cast: self::deferredCast($castClass),
            dataListType: $dataListType,
            dataListCastClass: $dataListCastClass,
            dataListCast: $dataListCast,
            dataCollectionType: $dataCollectionType,
            dataListTypeKind: self::nullableTypeKind($dataListType),
            dataCollectionCastClass: $dataCollectionCastClass,
            dataCollectionCast: $dataCollectionCast,
            dataCollectionTypeKind: self::nullableTypeKind($dataCollectionType),
            hasCollectionItemCast: $hasCollectionItemCast
                ?: $dataListCast instanceof CastInterface
                || $dataCollectionCast instanceof CastInterface
                || $lazyDataListCast instanceof CastInterface
                || $lazyDataCollectionCast instanceof CastInterface
                || $laravelCollectionCast instanceof CastInterface
                || $lazyLaravelCollectionCast instanceof CastInterface,
            validationRules: self::mixedList($payload['validationRules'] ?? null),
            itemValidationRules: self::mixedList($payload['itemValidationRules'] ?? null),
            parameter: $parameter,
            property: $property,
            collectionItemDescriptor: $collectionItemDescriptor,
            laravelCollectionType: $laravelCollectionType,
            laravelCollectionCastClass: $laravelCollectionCastClass,
            laravelCollectionCast: $laravelCollectionCast,
            laravelCollectionTypeKind: self::nullableTypeKind($laravelCollectionType),
            lazyLaravelCollectionType: $lazyLaravelCollectionType,
            lazyLaravelCollectionCastClass: $lazyLaravelCollectionCastClass,
            lazyLaravelCollectionCast: $lazyLaravelCollectionCast,
            lazyLaravelCollectionTypeKind: self::nullableTypeKind($lazyLaravelCollectionType),
            lazyDataListType: $lazyDataListType,
            lazyDataListCastClass: $lazyDataListCastClass,
            lazyDataListCast: $lazyDataListCast,
            lazyDataListTypeKind: self::nullableTypeKind($lazyDataListType),
            lazyDataCollectionType: $lazyDataCollectionType,
            lazyDataCollectionCastClass: $lazyDataCollectionCastClass,
            lazyDataCollectionCast: $lazyDataCollectionCast,
            lazyDataCollectionTypeKind: self::nullableTypeKind($lazyDataCollectionType),
            requiresHydrationContext: (bool) ($payload['requiresHydrationContext'] ?? false),
        );
    }

    /**
     * @param  list<string> $types
     * @return list<string>
     */
    public static function classifyTypes(array $types): array
    {
        return array_map(self::typeKind(...), $types);
    }

    public static function typeKind(string $type): string
    {
        return match (true) {
            $type === 'mixed' => 'mixed',
            $type === 'array' => 'array',
            $type === 'bool' => 'bool',
            $type === 'float' => 'float',
            $type === 'int' => 'int',
            $type === 'string' => 'string',
            enum_exists($type) && is_subclass_of($type, BackedEnum::class) => 'backed-enum',
            enum_exists($type) => 'unit-enum',
            is_subclass_of($type, AbstractData::class) => 'data',
            $type === DateTimeInterface::class || is_subclass_of($type, DateTimeInterface::class) => 'datetime',
            default => 'other',
        };
    }

    public static function nullableTypeKind(mixed $type): ?string
    {
        return is_string($type) ? self::typeKind($type) : null;
    }

    /**
     * @param list<string>                     $types
     * @param list<string>                     $typeKinds
     * @param null|class-string<CastInterface> $castClass
     */
    public static function buildCollectionItemDescriptor(
        array $types,
        array $typeKinds,
        ?string $castClass,
        ?CastInterface $cast,
    ): ?CollectionItemDescriptor {
        if (!$cast instanceof CastInterface && $types === ['mixed']) {
            return null;
        }

        return new CollectionItemDescriptor(
            types: $types,
            typeKinds: $typeKinds,
            castClass: $castClass,
            cast: $cast,
        );
    }

    /**
     * @return list<string>
     */
    public function collectionItemTypes(): array
    {
        if ($this->dataListType !== null) {
            return [$this->dataListType];
        }

        if ($this->dataCollectionType !== null) {
            return [$this->dataCollectionType];
        }

        if ($this->lazyDataListType !== null) {
            return [$this->lazyDataListType];
        }

        if ($this->lazyDataCollectionType !== null) {
            return [$this->lazyDataCollectionType];
        }

        if ($this->laravelCollectionType !== null) {
            return [$this->laravelCollectionType];
        }

        if ($this->lazyLaravelCollectionType !== null) {
            return [$this->lazyLaravelCollectionType];
        }

        return ['mixed'];
    }

    /**
     * @return list<string>
     */
    public function collectionItemTypeKinds(): array
    {
        if ($this->dataListType !== null) {
            return [$this->dataListTypeKind ?? 'other'];
        }

        if ($this->dataCollectionType !== null) {
            return [$this->dataCollectionTypeKind ?? 'other'];
        }

        if ($this->lazyDataListType !== null) {
            return [$this->lazyDataListTypeKind ?? 'other'];
        }

        if ($this->lazyDataCollectionType !== null) {
            return [$this->lazyDataCollectionTypeKind ?? 'other'];
        }

        if ($this->laravelCollectionType !== null) {
            return [$this->laravelCollectionTypeKind ?? 'other'];
        }

        if ($this->lazyLaravelCollectionType !== null) {
            return [$this->lazyLaravelCollectionTypeKind ?? 'other'];
        }

        return ['mixed'];
    }

    public function collectionItemCast(): ?CastInterface
    {
        return $this->dataListCast
            ?? $this->dataCollectionCast
            ?? $this->lazyDataListCast
            ?? $this->lazyDataCollectionCast
            ?? $this->laravelCollectionCast
            ?? $this->lazyLaravelCollectionCast;
    }

    public function collectionItemType(): ?string
    {
        return $this->dataListType
            ?? $this->dataCollectionType
            ?? $this->lazyDataListType
            ?? $this->lazyDataCollectionType
            ?? $this->laravelCollectionType
            ?? $this->lazyLaravelCollectionType;
    }

    public function collectionItemTypeKind(): ?string
    {
        return $this->dataListTypeKind
            ?? $this->dataCollectionTypeKind
            ?? $this->lazyDataListTypeKind
            ?? $this->lazyDataCollectionTypeKind
            ?? $this->laravelCollectionTypeKind
            ?? $this->lazyLaravelCollectionTypeKind;
    }

    public function forCollectionItem(): self
    {
        if ($this->collectionItemDescriptor instanceof CollectionItemDescriptor) {
            return $this->collectionItemDescriptor->toProperty(
                $this->name.'Item',
                $this->inputName,
                $this->outputName,
                $this->parameter,
                $this->property,
            );
        }

        return new self(
            name: $this->name.'Item',
            inputName: $this->inputName,
            outputName: $this->outputName,
            types: $this->collectionItemTypes(),
            typeKinds: $this->collectionItemTypeKinds(),
            nullable: false,
            hasDefaultValue: false,
            defaultValue: null,
            replaceEmptyStringsWithNull: false,
            inferValidationRules: false,
            isOptional: false,
            isSensitive: false,
            isEncrypted: false,
            isComputed: false,
            hasGeneratedValueAttribute: false,
            hasCollectionResultAttribute: false,
            hasCollectionSourceAttribute: false,
            hasCollectionTransformAttribute: false,
            hasLaravelCollectionTransformAttribute: false,
            hasLazyLaravelCollectionTransformAttribute: false,
            isLazy: false,
            computer: null,
            lazyResolver: null,
            lazyGroups: [],
            includeConditions: [],
            excludeConditions: [],
            castClass: $this->dataListCastClass
                ?? $this->dataCollectionCastClass
                ?? $this->lazyDataListCastClass
                ?? $this->lazyDataCollectionCastClass
                ?? $this->laravelCollectionCastClass,
            cast: $this->collectionItemCast(),
            dataListType: null,
            dataListCastClass: null,
            dataListCast: null,
            dataCollectionType: null,
            dataListTypeKind: null,
            dataCollectionCastClass: null,
            dataCollectionCast: null,
            dataCollectionTypeKind: null,
            hasCollectionItemCast: $this->hasCollectionItemCast,
            validationRules: [],
            itemValidationRules: [],
            parameter: $this->parameter,
            property: $this->property,
            laravelCollectionType: null,
            laravelCollectionCastClass: null,
            laravelCollectionCast: null,
            laravelCollectionTypeKind: null,
            lazyLaravelCollectionType: null,
            lazyLaravelCollectionCastClass: null,
            lazyLaravelCollectionCast: null,
            lazyLaravelCollectionTypeKind: null,
            lazyDataListType: null,
            lazyDataListCastClass: null,
            lazyDataListCast: null,
            lazyDataListTypeKind: null,
            lazyDataCollectionType: null,
            lazyDataCollectionCastClass: null,
            lazyDataCollectionCast: null,
            lazyDataCollectionTypeKind: null,
            requiresHydrationContext: false,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toCachePayload(): array
    {
        return [
            'name' => $this->name,
            'inputName' => $this->inputName,
            'outputName' => $this->outputName,
            'types' => $this->types,
            'typeKinds' => $this->typeKinds,
            'nullable' => $this->nullable,
            'hasDefaultValue' => $this->hasDefaultValue,
            'defaultValue' => $this->defaultValue,
            'replaceEmptyStringsWithNull' => $this->replaceEmptyStringsWithNull,
            'inferValidationRules' => $this->inferValidationRules,
            'isOptional' => $this->isOptional,
            'isSensitive' => $this->isSensitive,
            'isEncrypted' => $this->isEncrypted,
            'isComputed' => $this->isComputed,
            'hasGeneratedValueAttribute' => $this->hasGeneratedValueAttribute,
            'hasCollectionResultAttribute' => $this->hasCollectionResultAttribute,
            'hasCollectionSourceAttribute' => $this->hasCollectionSourceAttribute,
            'hasCollectionTransformAttribute' => $this->hasCollectionTransformAttribute,
            'hasLaravelCollectionTransformAttribute' => $this->hasLaravelCollectionTransformAttribute,
            'hasLazyLaravelCollectionTransformAttribute' => $this->hasLazyLaravelCollectionTransformAttribute,
            'isLazy' => $this->isLazy,
            'computer' => $this->computer,
            'lazyResolver' => $this->lazyResolver,
            'lazyGroups' => $this->lazyGroups,
            'includeConditions' => $this->includeConditions,
            'excludeConditions' => $this->excludeConditions,
            'castClass' => $this->castClass,
            'dataListType' => $this->dataListType,
            'dataListCastClass' => $this->dataListCastClass,
            'dataCollectionType' => $this->dataCollectionType,
            'dataCollectionCastClass' => $this->dataCollectionCastClass,
            'lazyDataListType' => $this->lazyDataListType,
            'lazyDataListCastClass' => $this->lazyDataListCastClass,
            'lazyDataCollectionType' => $this->lazyDataCollectionType,
            'lazyDataCollectionCastClass' => $this->lazyDataCollectionCastClass,
            'laravelCollectionType' => $this->laravelCollectionType,
            'laravelCollectionCastClass' => $this->laravelCollectionCastClass,
            'lazyLaravelCollectionType' => $this->lazyLaravelCollectionType,
            'lazyLaravelCollectionCastClass' => $this->lazyLaravelCollectionCastClass,
            'hasCollectionItemCast' => $this->hasCollectionItemCast,
            'requiresHydrationContext' => $this->requiresHydrationContext,
            'validationRules' => $this->validationRules,
            'itemValidationRules' => $this->itemValidationRules,
            'collectionItemDescriptor' => $this->collectionItemDescriptorPayload(),
            'propertyName' => $this->property?->getName(),
        ];
    }

    public function collectionItemDescriptor(): ?CollectionItemDescriptor
    {
        return $this->collectionItemDescriptor;
    }

    /**
     * @return array<int, mixed>
     */
    private static function mixedList(mixed $value): array
    {
        return is_array($value) ? array_values($value) : [];
    }

    /**
     * @param  list<string> $default
     * @return list<string>
     */
    private static function stringList(mixed $value, array $default = []): array
    {
        if (!is_array($value)) {
            return $default;
        }

        return array_values(array_filter($value, is_string(...)));
    }

    /**
     * @return null|class-string<CastInterface>
     */
    private static function castClass(mixed $value): ?string
    {
        return is_string($value) && is_subclass_of($value, CastInterface::class) ? $value : null;
    }

    private static function deferredCast(mixed $value): ?CastInterface
    {
        $class = self::castClass($value);

        if ($class === null) {
            return null;
        }

        return new LazyCast($class);
    }

    /**
     * @param array<string, mixed>             $payload
     * @param null|class-string<CastInterface> $dataListCastClass
     * @param null|class-string<CastInterface> $dataCollectionCastClass
     * @param null|class-string<CastInterface> $lazyLaravelCollectionCastClass
     * @param null|class-string<CastInterface> $lazyDataListCastClass
     * @param null|class-string<CastInterface> $lazyDataCollectionCastClass
     * @param null|class-string<CastInterface> $laravelCollectionCastClass
     */
    private static function collectionItemDescriptorFromPayload(
        array $payload,
        ?string $dataListType,
        ?string $dataCollectionType,
        ?string $lazyDataListType,
        ?string $lazyDataCollectionType,
        ?string $laravelCollectionType,
        ?string $lazyLaravelCollectionType,
        ?string $dataListCastClass,
        ?string $dataCollectionCastClass,
        ?string $lazyLaravelCollectionCastClass,
        ?string $lazyDataListCastClass,
        ?string $lazyDataCollectionCastClass,
        ?string $laravelCollectionCastClass,
        ?CastInterface $dataListCast,
        ?CastInterface $dataCollectionCast,
        ?CastInterface $lazyLaravelCollectionCast,
        ?CastInterface $lazyDataListCast,
        ?CastInterface $lazyDataCollectionCast,
        ?CastInterface $laravelCollectionCast,
    ): ?CollectionItemDescriptor {
        $descriptorPayload = $payload['collectionItemDescriptor'] ?? null;

        if (is_array($descriptorPayload)) {
            $types = self::stringList($descriptorPayload['types'] ?? null, ['mixed']);
            $typeKinds = self::typeKindList($descriptorPayload['typeKinds'] ?? null, $types);
            $castClass = self::castClass($descriptorPayload['castClass'] ?? null);

            return self::buildCollectionItemDescriptor(
                types: $types,
                typeKinds: $typeKinds,
                castClass: $castClass,
                cast: self::deferredCast($castClass),
            );
        }

        return self::buildCollectionItemDescriptor(
            types: $dataListType !== null
                ? [$dataListType]
                : ($dataCollectionType !== null
                    ? [$dataCollectionType]
                    : ($lazyDataListType !== null
                        ? [$lazyDataListType]
                        : ($lazyDataCollectionType !== null
                            ? [$lazyDataCollectionType]
                            : ($laravelCollectionType !== null
                                ? [$laravelCollectionType]
                                : ($lazyLaravelCollectionType !== null ? [$lazyLaravelCollectionType] : ['mixed']))))),
            typeKinds: $dataListType !== null
                ? [self::nullableTypeKind($dataListType) ?? 'other']
                : ($dataCollectionType !== null
                    ? [self::nullableTypeKind($dataCollectionType) ?? 'other']
                    : ($lazyDataListType !== null
                        ? [self::nullableTypeKind($lazyDataListType) ?? 'other']
                        : ($lazyDataCollectionType !== null
                            ? [self::nullableTypeKind($lazyDataCollectionType) ?? 'other']
                            : ($laravelCollectionType !== null
                                ? [self::nullableTypeKind($laravelCollectionType) ?? 'other']
                                : ($lazyLaravelCollectionType !== null
                                    ? [self::nullableTypeKind($lazyLaravelCollectionType) ?? 'other']
                                    : ['mixed']))))),
            castClass: $dataListCastClass
                ?? $dataCollectionCastClass
                ?? $lazyLaravelCollectionCastClass
                ?? $lazyDataListCastClass
                ?? $lazyDataCollectionCastClass
                ?? $laravelCollectionCastClass,
            cast: $dataListCast
                ?? $dataCollectionCast
                ?? $lazyLaravelCollectionCast
                ?? $lazyDataListCast
                ?? $lazyDataCollectionCast
                ?? $laravelCollectionCast,
        );
    }

    /**
     * @param  list<string> $types
     * @return list<string>
     */
    private static function typeKindList(mixed $value, array $types): array
    {
        if (!is_array($value)) {
            return self::classifyTypes($types);
        }

        return array_values(array_filter($value, is_string(...)));
    }

    /**
     * @return null|array{types: list<string>, typeKinds: list<string>, castClass: null|class-string<CastInterface>}
     */
    private function collectionItemDescriptorPayload(): ?array
    {
        if (!$this->collectionItemDescriptor instanceof CollectionItemDescriptor) {
            return null;
        }

        return [
            'types' => $this->collectionItemDescriptor->types,
            'typeKinds' => $this->collectionItemDescriptor->typeKinds,
            'castClass' => $this->collectionItemDescriptor->castClass,
        ];
    }
}
