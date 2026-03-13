<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Metadata;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Cline\AttributeReader\Attributes;
use Cline\Numerus\Numerus;
use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\AllowSuperfluousKeys;
use Cline\Struct\Attributes\AllowUndefinedValues;
use Cline\Struct\Attributes\AsDataCollection;
use Cline\Struct\Attributes\AsDataList;
use Cline\Struct\Attributes\CastWith;
use Cline\Struct\Attributes\Computed;
use Cline\Struct\Attributes\DoNotReplaceEmptyStringWithNull;
use Cline\Struct\Attributes\Encrypted;
use Cline\Struct\Attributes\ExcludeWhen;
use Cline\Struct\Attributes\ForbidSuperfluousKeys;
use Cline\Struct\Attributes\ForbidUndefinedValues;
use Cline\Struct\Attributes\IncludeWhen;
use Cline\Struct\Attributes\Lazy;
use Cline\Struct\Attributes\LazyGroup;
use Cline\Struct\Attributes\MapInputName;
use Cline\Struct\Attributes\MapInputNameUsing;
use Cline\Struct\Attributes\MapName;
use Cline\Struct\Attributes\MapOutputName;
use Cline\Struct\Attributes\MapOutputNameUsing;
use Cline\Struct\Attributes\ReplaceEmptyStringsWithNull;
use Cline\Struct\Attributes\StringifyUsing;
use Cline\Struct\Attributes\UseFactory;
use Cline\Struct\Attributes\UseModelPayloadResolver;
use Cline\Struct\Attributes\UseRequestPayloadResolver;
use Cline\Struct\Attributes\UseValidator;
use Cline\Struct\Attributes\WithInferredValidation;
use Cline\Struct\Attributes\WithoutInferredValidation;
use Cline\Struct\Casts\CarbonCast;
use Cline\Struct\Casts\CarbonInterfaceCast;
use Cline\Struct\Casts\DateTimeInterfaceCast;
use Cline\Struct\Casts\NumerusCast;
use Cline\Struct\Contracts\CastInterface;
use Cline\Struct\Contracts\ProvidesCastClassInterface;
use Cline\Struct\Contracts\ProvidesItemValidationRulesInterface;
use Cline\Struct\Contracts\ProvidesValidationRulesInterface;
use Cline\Struct\Enums\DataListType;
use Cline\Struct\Enums\NameMapper;
use Cline\Struct\Enums\SuperfluousKeys;
use Cline\Struct\Enums\UndefinedValues;
use Cline\Struct\Support\Optional;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Cache\Factory;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use SensitiveParameter;
use Spatie\StructureDiscoverer\Discover;
use Throwable;

use function array_filter;
use function array_key_exists;
use function array_unique;
use function array_values;
use function config;
use function function_exists;
use function in_array;
use function is_a;
use function is_array;
use function is_int;
use function is_string;
use function is_subclass_of;
use function resolve;

/**
 * Builds and caches metadata for data objects from reflection and attributes.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class MetadataFactory
{
    /** @var array<string, ClassMetadata> */
    private array $cache = [];

    /** @var null|list<class-string> */
    private ?array $discoveredClasses = null;

    /** @var null|list<class-string> */
    private ?array $cachedRegistryClasses = null;

    /** @var array<class-string<CastInterface>, CastInterface> */
    private array $buildCasts = [];

    private ?CacheRepository $cacheStoreRepository = null;

    /**
     * Resolve metadata for the given data object class.
     *
     * @param class-string $class
     */
    public function for(string $class): ClassMetadata
    {
        if (array_key_exists($class, $this->cache)) {
            return $this->cache[$class];
        }

        $cached = $this->cached($class);

        if ($cached instanceof ClassMetadata) {
            return $this->cache[$class] = $cached;
        }

        return $this->cache[$class] = $this->build($class);
    }

    public function clearRuntimeCache(): void
    {
        $this->cache = [];
        $this->discoveredClasses = null;
        $this->cachedRegistryClasses = null;
    }

    public function clearPersistentCache(): void
    {
        $store = $this->cacheStore();

        foreach ($this->cachedClasses() as $class) {
            $store->forget($this->cacheKey($class));
        }

        $store->forget($this->registryKey());
        $this->cachedRegistryClasses = [];
    }

    /**
     * @return list<class-string>
     */
    public function discoverDataClasses(): array
    {
        if (is_array($this->discoveredClasses)) {
            return $this->discoveredClasses;
        }

        if (!$this->reflectionDiscoveryEnabled()) {
            return $this->discoveredClasses = [];
        }

        $directories = $this->directories();

        if ($directories === []) {
            return $this->discoveredClasses = [];
        }

        $discovered = Discover::in(...$directories)
            ->classes()
            ->extending(AbstractData::class)
            ->get();

        $classes = [];

        foreach ($discovered as $class) {
            if (!is_string($class)) {
                continue;
            }

            if (!is_a($class, AbstractData::class, true)) {
                continue;
            }

            $classes[] = $class;
        }

        return $this->discoveredClasses = $classes;
    }

    /**
     * @param class-string $class
     */
    public function cacheKey(string $class): string
    {
        return $this->cachePrefix().':metadata:'.$class;
    }

    /**
     * @param class-string $class
     */
    private function build(string $class): ClassMetadata
    {
        $this->buildCasts = [];

        /** @var ReflectionClass<object> $reflection */
        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        /** @var null|MapName $classMap */
        $classMap = Attributes::get($class, MapName::class);

        /** @var null|MapInputNameUsing $classInputMap */
        $classInputMap = Attributes::get($class, MapInputNameUsing::class);

        /** @var null|MapOutputNameUsing $classOutputMap */
        $classOutputMap = Attributes::get($class, MapOutputNameUsing::class);
        $inferValidationRules = Attributes::has($class, WithInferredValidation::class)
            ? true
            : !Attributes::has($class, WithoutInferredValidation::class)
                && (bool) $this->config('struct.validation.infer_rules', true);
        $replaceEmptyStrings = Attributes::has($class, ReplaceEmptyStringsWithNull::class)
            ? true
            : !Attributes::has($class, DoNotReplaceEmptyStringWithNull::class)
                && (bool) $this->config('struct.replace_empty_strings_with_null', true);

        $properties = [];
        $reflectionProperties = [];

        foreach ($reflection->getProperties() as $property) {
            $reflectionProperties[$property->getName()] = $property;
        }

        foreach ($constructor?->getParameters() ?? [] as $parameter) {
            $property = $reflectionProperties[$parameter->getName()] ?? null;

            /** @var array<int, object> $propertyMap */
            $propertyMap = $property !== null ? Attributes::getAllOnProperty($class, $parameter->getName()) : [];
            $attributeMap = $this->inspectPropertyAttributes($propertyMap);
            $inputName = $this->inputName($parameter, $attributeMap, $classMap, $classInputMap);
            $outputName = $this->outputName($parameter, $attributeMap, $classMap, $classOutputMap);
            $types = $this->types($parameter->getType());
            $validationRules = $attributeMap->validationRules;
            $itemValidationRules = $attributeMap->itemValidationRules;

            /** @var null|class-string<CastInterface> $cast */
            $cast = is_string($attributeMap->castClass) && is_subclass_of($attributeMap->castClass, CastInterface::class)
                ? $attributeMap->castClass
                : $this->castClass($types);
            $dataList = $attributeMap->dataList;
            $dataCollection = $attributeMap->dataCollection;
            $dataListCast = $this->instantiateCast($dataList['castClass']);
            $dataCollectionCast = $this->instantiateCast($dataCollection['castClass']);
            $computed = $attributeMap->computed;
            $lazy = $attributeMap->lazy;
            $lazyGroups = $attributeMap->lazyGroups;
            $includeConditions = $attributeMap->includeConditions;
            $excludeConditions = $attributeMap->excludeConditions;
            $withoutReplacingEmptyStrings = $attributeMap->withoutReplacingEmptyStrings;
            $withPropertyInferredValidation = $attributeMap->withPropertyInferredValidation;
            $withoutPropertyInferredValidation = $attributeMap->withoutPropertyInferredValidation;
            $sensitive = $this->isSensitive($class, $parameter, $property);
            $collectionItemDescriptor = $this->collectionItemDescriptor(
                $dataList,
                $dataCollection,
                $dataListCast,
                $dataCollectionCast,
            );

            $properties[$parameter->getName()] = new PropertyMetadata(
                name: $parameter->getName(),
                inputName: $inputName,
                outputName: $outputName,
                types: $types,
                typeKinds: PropertyMetadata::classifyTypes($types),
                nullable: $parameter->allowsNull(),
                hasDefaultValue: $parameter->isDefaultValueAvailable(),
                defaultValue: $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
                replaceEmptyStringsWithNull: $replaceEmptyStrings
                    && !$withoutReplacingEmptyStrings,
                inferValidationRules: $withPropertyInferredValidation
                    ? true
                    : ($inferValidationRules
                        && !$withoutPropertyInferredValidation),
                isOptional: in_array(Optional::class, $types, true),
                isSensitive: $sensitive,
                isEncrypted: $attributeMap->isEncrypted,
                isComputed: $computed instanceof Computed,
                isLazy: $lazy instanceof Lazy || $lazyGroups !== [],
                computer: $computed?->computer,
                lazyResolver: $lazy?->resolver,
                lazyGroups: $lazyGroups,
                includeConditions: $includeConditions,
                excludeConditions: $excludeConditions,
                castClass: $cast,
                cast: $this->instantiateCast($cast),
                dataListType: $dataList['type'],
                dataListCastClass: $dataList['castClass'],
                dataListCast: $dataListCast,
                dataCollectionType: $dataCollection['type'],
                dataListTypeKind: PropertyMetadata::nullableTypeKind($dataList['type']),
                dataCollectionCastClass: $dataCollection['castClass'],
                dataCollectionCast: $dataCollectionCast,
                dataCollectionTypeKind: PropertyMetadata::nullableTypeKind($dataCollection['type']),
                hasCollectionItemCast: $dataListCast instanceof CastInterface || $dataCollectionCast instanceof CastInterface,
                validationRules: $validationRules,
                itemValidationRules: $itemValidationRules,
                parameter: $parameter,
                property: $property,
                collectionItemDescriptor: $collectionItemDescriptor,
            );
        }

        $metadata = new ClassMetadata(
            class: $class,
            reflection: $reflection,
            properties: $properties,
            forbidUndefinedValues: Attributes::has($class, AllowUndefinedValues::class)
                ? false
                : Attributes::has($class, ForbidUndefinedValues::class)
                    || $this->undefinedValuesPolicy() === UndefinedValues::Forbid,
            forbidSuperfluousKeys: Attributes::has($class, ForbidSuperfluousKeys::class)
                || (!Attributes::has($class, AllowSuperfluousKeys::class)
                    && $this->superfluousKeysPolicy() === SuperfluousKeys::Forbid),
            inferValidationRules: $inferValidationRules,
            validatorMutator: $this->validatorMutator($class),
            requestPayloadResolver: $this->requestPayloadResolver($class),
            modelPayloadResolver: $this->modelPayloadResolver($class),
            stringifier: $this->stringifier($class),
            factory: $this->factory($class),
        );

        $this->store($metadata);

        return $metadata;
    }

    /**
     * Resolve the input key name for a constructor parameter.
     */
    private function inputName(
        ReflectionParameter $parameter,
        PropertyAttributeMap $attributes,
        ?MapName $classMap,
        ?MapInputNameUsing $classInputMap,
    ): string {
        $explicit = $attributes->inputName;

        if ($explicit instanceof MapInputName) {
            return $explicit->name;
        }

        $inputMapped = $attributes->inputNameUsing;
        $inputMapper = $inputMapped instanceof MapInputNameUsing ? $inputMapped->mapper : $classInputMap?->mapper;

        if ($inputMapper instanceof NameMapper) {
            return $inputMapper->map($parameter->getName());
        }

        $mapped = $attributes->mapName;
        $mapper = $mapped instanceof MapName ? $mapped->mapper : $classMap?->mapper;

        return $mapper?->map($parameter->getName()) ?? $parameter->getName();
    }

    /**
     * Resolve the output key name for a constructor parameter.
     */
    private function outputName(
        ReflectionParameter $parameter,
        PropertyAttributeMap $attributes,
        ?MapName $classMap,
        ?MapOutputNameUsing $classOutputMap,
    ): string {
        $explicit = $attributes->outputName;

        if ($explicit instanceof MapOutputName) {
            return $explicit->name;
        }

        $outputMapped = $attributes->outputNameUsing;
        $outputMapper = $outputMapped instanceof MapOutputNameUsing ? $outputMapped->mapper : $classOutputMap?->mapper;

        if ($outputMapper instanceof NameMapper) {
            return $outputMapper->map($parameter->getName());
        }

        $mapped = $attributes->mapName;
        $mapper = $mapped instanceof MapName ? $mapped->mapper : $classMap?->mapper;

        return $mapper?->map($parameter->getName()) ?? $parameter->getName();
    }

    /**
     * @return list<string>
     */
    private function types(?ReflectionType $reflectionType): array
    {
        if (!$reflectionType instanceof ReflectionType) {
            return ['mixed'];
        }

        if ($reflectionType instanceof ReflectionNamedType) {
            return [$reflectionType->getName()];
        }

        if ($reflectionType instanceof ReflectionUnionType) {
            $types = [];

            foreach ($reflectionType->getTypes() as $type) {
                if (!$type instanceof ReflectionNamedType) {
                    continue;
                }

                $types[] = $type->getName();
            }

            return $types;
        }

        if ($reflectionType instanceof ReflectionIntersectionType) {
            $types = [];

            foreach ($reflectionType->getTypes() as $type) {
                if (!$type instanceof ReflectionNamedType) {
                    continue;
                }

                $types[] = $type->getName();
            }

            return $types;
        }

        return ['mixed'];
    }

    /**
     * @param  list<string>                     $types
     * @return null|class-string<CastInterface>
     */
    private function castClass(array $types): ?string
    {
        foreach ($types as $type) {
            if (
                $type === CarbonImmutable::class
                || $type === DateTimeImmutable::class
                || $type === DateTimeInterface::class
            ) {
                return DateTimeInterfaceCast::class;
            }

            if ($type === Carbon::class) {
                return CarbonCast::class;
            }

            if ($type === CarbonInterface::class) {
                return CarbonInterfaceCast::class;
            }

            if ($type === Numerus::class) {
                return NumerusCast::class;
            }
        }

        return null;
    }

    /**
     * @param array<int, object> $attributes
     */
    private function inspectPropertyAttributes(array $attributes): PropertyAttributeMap
    {
        if ($attributes === []) {
            return new PropertyAttributeMap(
                validationRules: [],
                itemValidationRules: [],
                lazyGroups: [],
                includeConditions: [],
                excludeConditions: [],
                castClass: null,
                dataList: ['type' => null, 'castClass' => null],
                dataCollection: ['type' => null, 'castClass' => null],
                computed: null,
                lazy: null,
                inputName: null,
                inputNameUsing: null,
                outputName: null,
                outputNameUsing: null,
                mapName: null,
                withoutReplacingEmptyStrings: false,
                isEncrypted: false,
                withPropertyInferredValidation: false,
                withoutPropertyInferredValidation: false,
            );
        }

        $validationRules = [];
        $itemValidationRules = [];
        $lazyGroups = [];
        $includeConditions = [];
        $excludeConditions = [];
        $castClass = null;
        $dataList = ['type' => null, 'castClass' => null];
        $dataCollection = ['type' => null, 'castClass' => null];
        $computed = null;
        $lazy = null;
        $inputName = null;
        $inputNameUsing = null;
        $outputName = null;
        $outputNameUsing = null;
        $mapName = null;
        $withoutReplacingEmptyStrings = false;
        $isEncrypted = false;
        $withPropertyInferredValidation = false;
        $withoutPropertyInferredValidation = false;

        foreach ($attributes as $attribute) {
            if ($attribute instanceof ProvidesValidationRulesInterface) {
                foreach ($attribute->rules() as $rule) {
                    $validationRules[] = $rule;
                }
            }

            if ($attribute instanceof ProvidesItemValidationRulesInterface) {
                foreach ($attribute->rules() as $rule) {
                    $itemValidationRules[] = $rule;
                }
            }

            if ($attribute instanceof LazyGroup) {
                $lazyGroups[] = $attribute->group;

                continue;
            }

            if ($attribute instanceof IncludeWhen) {
                $includeConditions[] = $attribute->condition;

                continue;
            }

            if ($attribute instanceof ExcludeWhen) {
                $excludeConditions[] = $attribute->condition;

                continue;
            }

            if ($attribute instanceof CastWith) {
                $castClass = $attribute->cast;

                continue;
            }

            if ($attribute instanceof ProvidesCastClassInterface) {
                $castClass = $attribute->castClass();

                continue;
            }

            if ($attribute instanceof AsDataList) {
                $dataList = $this->normalizeCollectionDescriptor($attribute->descriptor);

                continue;
            }

            if ($attribute instanceof AsDataCollection) {
                $dataCollection = $this->normalizeCollectionDescriptor($attribute->descriptor);

                continue;
            }

            if ($attribute instanceof Computed) {
                $computed = $attribute;

                continue;
            }

            if ($attribute instanceof Lazy) {
                $lazy = $attribute;

                continue;
            }

            if ($attribute instanceof MapInputName) {
                $inputName = $attribute;

                continue;
            }

            if ($attribute instanceof MapInputNameUsing) {
                $inputNameUsing = $attribute;

                continue;
            }

            if ($attribute instanceof MapOutputName) {
                $outputName = $attribute;

                continue;
            }

            if ($attribute instanceof MapOutputNameUsing) {
                $outputNameUsing = $attribute;

                continue;
            }

            if ($attribute instanceof MapName) {
                $mapName = $attribute;

                continue;
            }

            if ($attribute instanceof DoNotReplaceEmptyStringWithNull) {
                $withoutReplacingEmptyStrings = true;

                continue;
            }

            if ($attribute instanceof Encrypted) {
                $isEncrypted = true;

                continue;
            }

            if ($attribute instanceof WithInferredValidation) {
                $withPropertyInferredValidation = true;

                continue;
            }

            if (!$attribute instanceof WithoutInferredValidation) {
                continue;
            }

            $withoutPropertyInferredValidation = true;
        }

        return new PropertyAttributeMap(
            validationRules: $validationRules,
            itemValidationRules: $itemValidationRules,
            lazyGroups: $lazyGroups,
            includeConditions: $includeConditions,
            excludeConditions: $excludeConditions,
            castClass: $castClass,
            dataList: $dataList,
            dataCollection: $dataCollection,
            computed: $computed,
            lazy: $lazy,
            inputName: $inputName,
            inputNameUsing: $inputNameUsing,
            outputName: $outputName,
            outputNameUsing: $outputNameUsing,
            mapName: $mapName,
            withoutReplacingEmptyStrings: $withoutReplacingEmptyStrings,
            isEncrypted: $isEncrypted,
            withPropertyInferredValidation: $withPropertyInferredValidation,
            withoutPropertyInferredValidation: $withoutPropertyInferredValidation,
        );
    }

    /**
     * @param null|class-string<CastInterface> $class
     */
    private function instantiateCast(?string $class): ?CastInterface
    {
        if ($class === null) {
            return null;
        }

        if (isset($this->buildCasts[$class])) {
            return $this->buildCasts[$class];
        }

        $cast = new LazyCast($class);

        return $this->buildCasts[$class] = $cast;
    }

    /**
     * @param array{type: null|string, castClass: null|class-string<CastInterface>} $dataList
     * @param array{type: null|string, castClass: null|class-string<CastInterface>} $dataCollection
     */
    private function collectionItemDescriptor(
        array $dataList,
        array $dataCollection,
        ?CastInterface $dataListCast,
        ?CastInterface $dataCollectionCast,
    ): ?CollectionItemDescriptor {
        $type = $dataList['type'] ?? $dataCollection['type'] ?? 'mixed';

        return PropertyMetadata::buildCollectionItemDescriptor(
            types: [$type],
            typeKinds: [$type === 'mixed' ? 'mixed' : (PropertyMetadata::nullableTypeKind($type) ?? 'other')],
            castClass: $dataList['castClass'] ?? $dataCollection['castClass'],
            cast: $dataListCast ?? $dataCollectionCast,
        );
    }

    /**
     * @param  class-string|DataListType                                             $descriptor
     * @return array{type: null|string, castClass: null|class-string<CastInterface>}
     */
    private function normalizeCollectionDescriptor(string|DataListType $descriptor): array
    {
        $resolved = $this->normalizeCollectionType($descriptor);

        if (is_a($resolved, CastInterface::class, true)) {
            return [
                'type' => null,
                'castClass' => $resolved,
            ];
        }

        return [
            'type' => $resolved,
            'castClass' => null,
        ];
    }

    /**
     * @param class-string $class
     */
    private function isSensitive(
        string $class,
        ReflectionParameter $parameter,
        ?ReflectionProperty $property,
    ): bool {
        if ($property instanceof ReflectionProperty && $property->getAttributes(SensitiveParameter::class) !== []) {
            return true;
        }

        return Attributes::getAllOnParameter($class, '__construct', $parameter->getName(), SensitiveParameter::class) !== [];
    }

    /**
     * @param class-string|DataListType $type
     */
    private function normalizeCollectionType(string|DataListType $type): string
    {
        return $type instanceof DataListType ? $type->value : $type;
    }

    /**
     * @param class-string $class
     */
    private function stringifier(string $class): ?string
    {
        $stringifier = Attributes::get($class, StringifyUsing::class);

        return $stringifier instanceof StringifyUsing ? $stringifier->stringifier : null;
    }

    /**
     * @param class-string $class
     */
    private function factory(string $class): ?string
    {
        $factory = Attributes::get($class, UseFactory::class);

        return $factory instanceof UseFactory ? $factory->factory : null;
    }

    /**
     * @param class-string $class
     */
    private function validatorMutator(string $class): ?string
    {
        $mutator = Attributes::get($class, UseValidator::class);

        return $mutator instanceof UseValidator ? $mutator->mutator : null;
    }

    /**
     * @param class-string $class
     */
    private function requestPayloadResolver(string $class): ?string
    {
        $resolver = Attributes::get($class, UseRequestPayloadResolver::class);

        return $resolver instanceof UseRequestPayloadResolver ? $resolver->resolver : null;
    }

    /**
     * @param class-string $class
     */
    private function modelPayloadResolver(string $class): ?string
    {
        $resolver = Attributes::get($class, UseModelPayloadResolver::class);

        return $resolver instanceof UseModelPayloadResolver ? $resolver->resolver : null;
    }

    private function config(string $key, mixed $default): mixed
    {
        if (!function_exists('config')) {
            return $default;
        }

        try {
            return config($key, $default);
        } catch (Throwable) {
            return $default;
        }
    }

    private function undefinedValuesPolicy(): UndefinedValues
    {
        $value = $this->config('struct.undefined_values', UndefinedValues::Allow);

        if ($value instanceof UndefinedValues) {
            return $value;
        }

        if (!is_string($value)) {
            return UndefinedValues::Allow;
        }

        return UndefinedValues::tryFrom($value) ?? UndefinedValues::Allow;
    }

    private function superfluousKeysPolicy(): SuperfluousKeys
    {
        $value = $this->config('struct.superfluous_keys', SuperfluousKeys::Allow);

        if ($value instanceof SuperfluousKeys) {
            return $value;
        }

        if ($value === 'ignore') {
            return SuperfluousKeys::Allow;
        }

        if (!is_string($value)) {
            return SuperfluousKeys::Allow;
        }

        return SuperfluousKeys::tryFrom($value) ?? SuperfluousKeys::Allow;
    }

    /**
     * @param class-string $class
     */
    private function cached(string $class): ?ClassMetadata
    {
        if (!$this->persistentCachingEnabled()) {
            return null;
        }

        $payload = $this->cacheStore()->get($this->cacheKey($class));

        if (!is_array($payload)) {
            return null;
        }

        /** @var array<string, mixed> $payload */
        return ClassMetadata::fromCachePayload($payload);
    }

    private function store(ClassMetadata $metadata): void
    {
        if (!$this->persistentCachingEnabled()) {
            return;
        }

        $store = $this->cacheStore();

        $duration = $this->cacheDuration();
        $key = $this->cacheKey($metadata->class);
        $payload = $metadata->toCachePayload();

        if (is_int($duration)) {
            $store->put($key, $payload, $duration);
        } else {
            $store->forever($key, $payload);
        }

        $classes = $this->cachedClasses();
        $classes[] = $metadata->class;
        $classes = array_values(array_unique($classes));
        $this->cachedRegistryClasses = $classes;

        $store->forever($this->registryKey(), $classes);
    }

    /**
     * @return list<class-string>
     */
    private function cachedClasses(): array
    {
        if (is_array($this->cachedRegistryClasses)) {
            return $this->cachedRegistryClasses;
        }

        $classes = $this->cacheStore()->get($this->registryKey(), []);

        return $this->cachedRegistryClasses = is_array($classes)
            ? array_values(array_filter($classes, static fn (mixed $class): bool => is_string($class) && is_a($class, AbstractData::class, true)))
            : [];
    }

    private function cacheStore(): CacheRepository
    {
        if ($this->cacheStoreRepository instanceof CacheRepository) {
            return $this->cacheStoreRepository;
        }

        /** @var CacheManager $cache */
        $cache = resolve(Factory::class);

        return $this->cacheStoreRepository = $cache->store($this->cacheStoreName());
    }

    private function persistentCachingEnabled(): bool
    {
        return (bool) $this->config('struct.structure_caching.enabled', false);
    }

    private function cacheStoreName(): ?string
    {
        $value = $this->config('struct.structure_caching.cache.store', null);

        return is_string($value) ? $value : null;
    }

    private function cachePrefix(): string
    {
        $value = $this->config('struct.structure_caching.cache.prefix', 'struct');

        return is_string($value) && $value !== '' ? $value : 'struct';
    }

    private function cacheDuration(): ?int
    {
        $value = $this->config('struct.structure_caching.cache.duration', null);

        return is_int($value) ? $value : null;
    }

    private function registryKey(): string
    {
        return $this->cachePrefix().':registry';
    }

    /**
     * @return list<string>
     */
    private function directories(): array
    {
        $value = $this->config('struct.structure_caching.directories', []);

        return is_array($value)
            ? array_values(array_filter($value, static fn (mixed $directory): bool => is_string($directory) && $directory !== ''))
            : [];
    }

    private function reflectionDiscoveryEnabled(): bool
    {
        return (bool) $this->config('struct.structure_caching.reflection_discovery.enabled', false);
    }
}

/**
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 * @internal
 */
final readonly class PropertyAttributeMap
{
    /**
     * @param array<int, mixed>                                                     $validationRules
     * @param array<int, mixed>                                                     $itemValidationRules
     * @param array<int, string>                                                    $lazyGroups
     * @param array<int, string>                                                    $includeConditions
     * @param array<int, string>                                                    $excludeConditions
     * @param array{type: null|string, castClass: null|class-string<CastInterface>} $dataList
     * @param array{type: null|string, castClass: null|class-string<CastInterface>} $dataCollection
     */
    public function __construct(
        public array $validationRules,
        public array $itemValidationRules,
        public array $lazyGroups,
        public array $includeConditions,
        public array $excludeConditions,
        public ?string $castClass,
        public array $dataList,
        public array $dataCollection,
        public ?Computed $computed,
        public ?Lazy $lazy,
        public ?MapInputName $inputName,
        public ?MapInputNameUsing $inputNameUsing,
        public ?MapOutputName $outputName,
        public ?MapOutputNameUsing $outputNameUsing,
        public ?MapName $mapName,
        public bool $withoutReplacingEmptyStrings,
        public bool $isEncrypted,
        public bool $withPropertyInferredValidation,
        public bool $withoutPropertyInferredValidation,
    ) {}
}
