<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct;

use BackedEnum;
use Cline\Struct\Attributes\Collections\AbstractCollectionTransformer;
use Cline\Struct\Contracts\CastInterface;
use Cline\Struct\Contracts\ComputesCollectionResultValueInterface;
use Cline\Struct\Contracts\ComputesValueInterface;
use Cline\Struct\Contracts\ContextualCastInterface;
use Cline\Struct\Contracts\ContextualTransformsCollectionValueInterface;
use Cline\Struct\Contracts\DataObjectInterface;
use Cline\Struct\Contracts\GeneratesCollectionValueInterface;
use Cline\Struct\Contracts\GeneratesMissingValueInterface;
use Cline\Struct\Contracts\ResolvesLazyValueInterface;
use Cline\Struct\Contracts\SerializationConditionInterface;
use Cline\Struct\Contracts\StringifierInterface;
use Cline\Struct\Contracts\TransformsCollectionValueInterface;
use Cline\Struct\Contracts\TransformsLaravelCollectionValueInterface;
use Cline\Struct\Contracts\TransformsLazyCollectionValueInterface;
use Cline\Struct\Contracts\WrapsLaravelCollectionTransformInterface;
use Cline\Struct\Eloquent\AsData;
use Cline\Struct\Eloquent\AsDataCollection;
use Cline\Struct\Exceptions\CursorPaginatorCollectTargetException;
use Cline\Struct\Exceptions\DataValidationException;
use Cline\Struct\Exceptions\InvalidArrayCollectTargetException;
use Cline\Struct\Exceptions\InvalidCollectionAttributeException;
use Cline\Struct\Exceptions\InvalidCollectionCollectTargetException;
use Cline\Struct\Exceptions\InvalidEloquentCollectionCollectTargetException;
use Cline\Struct\Exceptions\InvalidGeneratedValueAttributeException;
use Cline\Struct\Exceptions\LengthAwarePaginatorCollectTargetException;
use Cline\Struct\Exceptions\MissingDataValueException;
use Cline\Struct\Exceptions\RequestDataValidationException;
use Cline\Struct\Exceptions\SuperfluousInputKeyException;
use Cline\Struct\Exceptions\UnsupportedFactoryException;
use Cline\Struct\Factories\AbstractFactory;
use Cline\Struct\Metadata\ClassMetadata;
use Cline\Struct\Metadata\CollectionItemRuntime;
use Cline\Struct\Metadata\MetadataFactory;
use Cline\Struct\Metadata\PropertyMetadata;
use Cline\Struct\Serialization\DataSerializer;
use Cline\Struct\Serialization\SerializationContext;
use Cline\Struct\Serialization\SerializationOptions;
use Cline\Struct\Support\CreationContext;
use Cline\Struct\Support\DataCollection;
use Cline\Struct\Support\DataList;
use Cline\Struct\Support\DateFormat;
use Cline\Struct\Support\HydrationGuard;
use Cline\Struct\Support\LazyCollectionState;
use Cline\Struct\Support\LazyDataCollection;
use Cline\Struct\Support\LazyDataList;
use Cline\Struct\Support\Optional;
use Cline\Struct\Support\PropertyHydrationContext;
use Cline\Struct\Support\RecursionGuard;
use Cline\Struct\Support\StringifierResolver;
use Cline\Struct\Validation\ValidationFactory;
use Closure;
use DateTimeInterface;
use EmptyIterator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use ReflectionAttribute;
use stdClass;
use Stringable;
use Throwable;
use Traversable;
use UnitEnum;

use const JSON_THROW_ON_ERROR;

use function array_key_exists;
use function array_keys;
use function array_map;
use function array_values;
use function constant;
use function get_object_vars;
use function in_array;
use function is_a;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_iterable;
use function is_numeric;
use function is_object;
use function is_string;
use function json_encode;
use function mb_trim;
use function resolve;
use function throw_if;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
abstract readonly class AbstractData implements DataObjectInterface, Stringable
{
    public function __toString(): string
    {
        $stringifier = static::metadata()->stringifier;

        if ($stringifier !== null) {
            /** @var class-string<StringifierInterface> $stringifier */
            /** @var null|StringifierInterface $instance */
            $instance = null;

            try {
                $instance = resolve(StringifierResolver::class)->resolve($stringifier);
            } catch (Throwable) {
                $instance = new $stringifier();
            }

            if ($instance !== null) {
                return $instance->stringify($this, resolve(SerializationOptions::class));
            }

            return $this->toJson();
        }

        return $this->toJson();
    }

    /**
     * @param array<string, mixed> $input
     */
    public static function create(array $input): static
    {
        $context = static::creationContext();

        return static::createFromInput($context, $input);
    }

    /**
     * @param array<string, mixed> $input
     */
    public static function createWithValidation(array $input): static
    {
        $context = static::creationContext();

        return static::createFromInputWithValidation($context, $input);
    }

    /**
     * @param array<string, mixed>|Arrayable<string, mixed>|Model $source
     */
    public static function createFromModel(array|Arrayable|Model $source): static
    {
        $context = static::creationContext();

        return static::createFromInput(
            $context,
            $context->modelPayloadResolver()->resolve($source, static::class),
        );
    }

    /**
     * @param array<string, mixed>|Arrayable<string, mixed>|Model $source
     */
    public static function createFromModelWithValidation(array|Arrayable|Model $source): static
    {
        $context = static::creationContext();
        $input = $context->modelPayloadResolver()->resolve($source, static::class);

        return static::createFromInputWithValidation($context, $input);
    }

    public static function createFromRequest(Request $request): static
    {
        $context = static::creationContext();
        $input = $context->requestPayloadResolver()->resolve($request, static::class);

        return static::createFromInput($context, $input);
    }

    public static function createFromRequestWithValidation(Request $request): static
    {
        $context = static::creationContext();
        $input = $context->requestPayloadResolver()->resolve($request, static::class);

        return static::createFromInputWithValidation($context, $input, true);
    }

    /**
     * @param  array<array-key, mixed>|Collection<array-key, mixed>|CursorPaginator<array-key, mixed>|LengthAwarePaginator<array-key, mixed>     $items
     * @return array<array-key, static>|Collection<array-key, static>|CursorPaginator<array-key, static>|LengthAwarePaginator<array-key, static>
     */
    public static function collect(
        array|Collection|LengthAwarePaginator|CursorPaginator $items,
    ): array|Collection|LengthAwarePaginator|CursorPaginator {
        return static::collectMapped($items);
    }

    /**
     * @param  array<array-key, mixed>|Collection<array-key, mixed>|CursorPaginator<array-key, mixed>|LengthAwarePaginator<array-key, mixed>     $items
     * @param  'array'|class-string                                                                                                              $into
     * @return array<array-key, static>|Collection<array-key, static>|CursorPaginator<array-key, static>|LengthAwarePaginator<array-key, static>
     */
    public static function collectInto(
        array|Collection|LengthAwarePaginator|CursorPaginator $items,
        string $into,
    ): array|Collection|LengthAwarePaginator|CursorPaginator {
        return static::collectMapped($items, $into);
    }

    public static function factory(): AbstractFactory
    {
        $factory = static::metadata()->factory;

        if ($factory === null) {
            throw UnsupportedFactoryException::forDataObject(static::class);
        }

        /** @var AbstractFactory $instance */
        $instance = $factory::new();

        return $instance;
    }

    /**
     * @param array<string, mixed> $arguments
     */
    public static function castUsing(array $arguments): AsData
    {
        return new AsData(static::class);
    }

    public static function castAsCollection(): AsDataCollection
    {
        return AsDataCollection::of(static::class);
    }

    /**
     * Internal value serializer for collection wrappers that need to reuse
     * traversal state across arbitrary values.
     *
     * @internal
     */
    public static function serializeValueUsingContext(
        mixed $value,
        SerializationContext $context,
    ): mixed {
        return static::serializeAny($value, $context);
    }

    public function with(mixed ...$overrides): static
    {
        $payload = get_object_vars($this);

        foreach ($overrides as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            $payload[$key] = $value;
        }

        return static::createFromInput(static::creationContext(), $payload);
    }

    public function serializer(?SerializationOptions $options = null): DataSerializer
    {
        return new DataSerializer($this, $options ?? resolve(SerializationOptions::class));
    }

    /**
     * @param array<int, string>   $include
     * @param array<int, string>   $exclude
     * @param array<int, string>   $groups
     * @param array<string, mixed> $context
     */
    public function toArray(
        bool $includeSensitive = false,
        array $include = [],
        array $exclude = [],
        array $groups = [],
        array $context = [],
        ?SerializationOptions $serialization = null,
    ): array {
        $options = $serialization;

        if (!$options instanceof SerializationOptions) {
            $isDefaultProjection = $include === []
                && $exclude === []
                && $groups === []
                && $context === [];
            $options = $isDefaultProjection
                ? ($includeSensitive
                    ? resolve(SerializationOptions::class)->withSensitive()
                    : resolve(SerializationOptions::class))
                : new SerializationOptions(
                    includeSensitive: $includeSensitive,
                    include: $include,
                    exclude: $exclude,
                    groups: $groups,
                    context: $context,
                );
        } else {
            $isDefaultProjection = $options->usesDefaultProjection();
        }

        return $isDefaultProjection
            ? $this->serializeDefaultPayloadUsingContext(
                new SerializationContext(
                    new RecursionGuard(),
                    $options,
                ),
            )
            : $this->serializePayload(
                new SerializationContext(
                    new RecursionGuard(),
                    $options,
                ),
            );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Internal serializer hook for collection wrappers that need to reuse
     * traversal state across multiple DTO items.
     *
     * @internal
     * @return array<string, mixed>
     */
    public function toArrayUsingContext(
        SerializationContext $context,
    ): array {
        return $context->usesDefaultProjection()
            ? $this->serializeDefaultPayloadUsingContext($context)
            : $this->serializePayload($context);
    }

    /**
     * @param mixed                $options
     * @param array<int, string>   $include
     * @param array<int, string>   $exclude
     * @param array<int, string>   $groups
     * @param array<string, mixed> $context
     */
    public function toJson(
        $options = 0,
        bool $includeSensitive = false,
        array $include = [],
        array $exclude = [],
        array $groups = [],
        array $context = [],
        ?SerializationOptions $serialization = null,
    ): string {
        $jsonOptions = is_int($options) ? $options : 0;

        return json_encode(
            $this->toArray(
                includeSensitive: $includeSensitive,
                include: $include,
                exclude: $exclude,
                groups: $groups,
                context: $context,
                serialization: $serialization,
            ),
            JSON_THROW_ON_ERROR | $jsonOptions,
        );
    }

    /**
     * @internal
     * @param  array<array-key, mixed>|Collection<array-key, mixed>|CursorPaginator<array-key, mixed>|LengthAwarePaginator<array-key, mixed>     $items
     * @param  null|'array'|class-string                                                                                                         $into
     * @return array<array-key, static>|Collection<array-key, static>|CursorPaginator<array-key, static>|LengthAwarePaginator<array-key, static>
     */
    protected static function collectMapped(
        array|Collection|LengthAwarePaginator|CursorPaginator $items,
        ?string $into = null,
    ): array|Collection|LengthAwarePaginator|CursorPaginator {
        if ($items instanceof LengthAwarePaginator) {
            return static::collectLengthAwarePaginator($items, $into);
        }

        if ($items instanceof CursorPaginator) {
            return static::collectCursorPaginator($items, $into);
        }

        if ($items instanceof EloquentCollection) {
            return static::collectEloquentCollection($items, $into);
        }

        if ($items instanceof Collection) {
            return static::collectCollection($items, $into);
        }

        return static::collectArray($items, $into);
    }

    /**
     * @internal
     */
    protected static function metadata(): ClassMetadata
    {
        try {
            return resolve(MetadataFactory::class)->for(static::class);
        } catch (Throwable) {
            return new MetadataFactory()->for(static::class);
        }
    }

    /**
     * @internal
     * @param array<array-key, mixed> $input
     */
    protected static function createFromInput(
        CreationContext $context,
        array $input,
        bool $cascadeValidation = false,
    ): static {
        $metadata = $context->metadata;

        /** @var array<string, mixed> $rawInput */
        $rawInput = $input;
        $context->beginHydration($rawInput);
        $input = static::prepareInput($metadata, $input, $context);
        static::assertNoRecursiveInput($input, $context);
        static::assertNoSuperfluousKeys($metadata, $input);

        $values = [];
        $tracksContextualHydration = $metadata->usesContextualHydration;

        foreach ($metadata->hydratedProperties as $property) {
            if ($tracksContextualHydration) {
                $context->setProperties($values);
            }

            $values[$property->name] = static::resolveValue($metadata, $property, $input, $cascadeValidation, $context);
        }

        if ($tracksContextualHydration) {
            $context->setProperties($values);
        }

        foreach ($metadata->postHydrationProperties as $property) {
            $values[$property->name] = static::resolveHydratedLaravelCollectionValue($property, $values, $metadata, $context);
            $values[$property->name] = static::resolveHydratedLazyLaravelCollectionValue($property, $values, $metadata, $context);

            if (!$tracksContextualHydration) {
                continue;
            }

            $context->setProperties($values);
        }

        foreach ($metadata->collectionSourceProperties as $property) {
            $values[$property->name] = static::resolveGeneratedCollectionValue($context, $property, $values);
        }

        foreach ($metadata->postHydrationCollectionSourceProperties as $property) {
            $values[$property->name] = static::resolveHydratedLaravelCollectionValue($property, $values, $metadata, $context);
            $values[$property->name] = static::resolveHydratedLazyLaravelCollectionValue($property, $values, $metadata, $context);

            if (!$tracksContextualHydration) {
                continue;
            }

            $context->setProperties($values);
        }

        foreach ($metadata->collectionResultProperties as $property) {
            $values[$property->name] = static::resolveCollectionResultValue($context, $property, $values);
        }

        foreach ($metadata->computedProperties as $property) {
            if ($property->isLazy) {
                $values[$property->name] = $property->hasDefaultValue ? $property->defaultValue : null;

                continue;
            }

            $values[$property->name] = static::resolveComputedValue($context, $property, $values);
        }

        /** @phpstan-ignore-next-line */
        return new static(...$values);
    }

    /**
     * @internal
     * @param array<string, mixed> $input
     */
    protected static function createFromInputWithValidation(
        CreationContext $context,
        array $input,
        bool $requestException = false,
    ): static {
        $context->beginHydration($input);
        $input = static::prepareInput($context->metadata, $input, $context);

        /** @var array<string, mixed> $input */
        $prepared = $context->validationFactory()->make($context->metadata, $input);
        $validator = $prepared->validator;

        if ($validator->fails()) {
            throw_if(
                $requestException,
                RequestDataValidationException::fromValidator($validator, $prepared->errorBag),
            );

            throw DataValidationException::fromValidator($validator, $prepared->errorBag);
        }

        return static::createFromInput($context, $input, true);
    }

    /**
     * @internal
     * @param  array<array-key, mixed> $input
     * @return array<array-key, mixed>
     */
    protected static function prepareInput(ClassMetadata $metadata, array $input, ?CreationContext $context = null): array
    {
        foreach ($metadata->hydratedProperties as $property) {
            $attribute = static::generatedValueAttribute($metadata, $property, $context);

            if (!$attribute instanceof GeneratesMissingValueInterface) {
                continue;
            }

            if (static::resolveInputValue($property, $input)[0]) {
                continue;
            }

            $input[$property->inputName] = $attribute->generate();
        }

        return $input;
    }

    /**
     * @internal
     */
    protected static function validationFactory(): ValidationFactory
    {
        try {
            return resolve(ValidationFactory::class);
        } catch (Throwable) {
            return new ValidationFactory();
        }
    }

    /**
     * @internal
     */
    protected static function creationContext(): CreationContext
    {
        return new CreationContext(static::metadata());
    }

    /**
     * @internal
     * @param  array<array-key, mixed>                                $items
     * @return array<array-key, static>|Collection<array-key, static>
     */
    protected static function collectArray(array $items, ?string $into = null): array|Collection
    {
        $context = static::creationContext();
        $mapped = [];

        foreach ($items as $key => $item) {
            $mapped[$key] = static::mapCollectedItem($context, $item);
        }

        return match ($into) {
            null, 'array' => $mapped,
            Collection::class => new Collection($mapped),
            EloquentCollection::class => static::eloquentCollectionFromMappedItems($mapped),
            default => throw InvalidArrayCollectTargetException::fromTarget($into),
        };
    }

    /**
     * @internal
     * @param  Collection<array-key, mixed>  $items
     * @return Collection<array-key, static>
     */
    protected static function collectCollection(Collection $items, ?string $into = null): Collection
    {
        $context = static::creationContext();
        $mapped = [];

        foreach ($items as $key => $item) {
            $mapped[$key] = static::mapCollectedItem($context, $item);
        }

        return match ($into) {
            null, Collection::class => new Collection($mapped),
            EloquentCollection::class => static::eloquentCollectionFromMappedItems($mapped),
            default => throw InvalidCollectionCollectTargetException::fromTarget($into),
        };
    }

    /**
     * @internal
     * @param  EloquentCollection<array-key, Model> $items
     * @return Collection<array-key, static>
     */
    protected static function collectEloquentCollection(EloquentCollection $items, ?string $into = null): Collection
    {
        $context = static::creationContext();
        $mapped = [];

        foreach ($items as $key => $item) {
            $mapped[$key] = static::mapCollectedItem($context, $item);
        }

        return match ($into) {
            null, EloquentCollection::class => static::eloquentCollectionFromMappedItems($mapped),
            Collection::class => new Collection($mapped),
            default => throw InvalidEloquentCollectionCollectTargetException::fromTarget($into),
        };
    }

    /**
     * @internal
     * @param  LengthAwarePaginator<array-key, mixed>  $items
     * @return LengthAwarePaginator<array-key, static>
     */
    protected static function collectLengthAwarePaginator(LengthAwarePaginator $items, ?string $into = null): LengthAwarePaginator
    {
        throw_if(
            $into !== null && $into !== LengthAwarePaginator::class,
            LengthAwarePaginatorCollectTargetException::fromTarget($into),
        );

        $context = static::creationContext();
        $mapped = [];

        foreach ($items->items() as $key => $item) {
            $mapped[$key] = static::mapCollectedItem($context, $item);
        }

        return new LengthAwarePaginator(
            $mapped,
            $items->total(),
            $items->perPage(),
            $items->currentPage(),
            $items->getOptions(),
        );
    }

    /**
     * @internal
     * @param  CursorPaginator<array-key, mixed>  $items
     * @return CursorPaginator<array-key, static>
     */
    protected static function collectCursorPaginator(CursorPaginator $items, ?string $into = null): CursorPaginator
    {
        throw_if(
            $into !== null && $into !== CursorPaginator::class,
            CursorPaginatorCollectTargetException::fromTarget($into),
        );

        $context = static::creationContext();
        $mapped = [];

        foreach ($items->items() as $key => $item) {
            $mapped[$key] = static::mapCollectedItem($context, $item);
        }

        return new CursorPaginator(
            $mapped,
            $items->perPage(),
            $items->cursor(),
            $items->getOptions(),
        );
    }

    /**
     * @internal
     */
    protected static function mapCollectedItem(CreationContext $context, mixed $item): static
    {
        if ($item instanceof static) {
            return $item;
        }

        if ($item instanceof Model) {
            return static::createFromInput(
                $context,
                $context->modelPayloadResolver()->resolve($item, static::class),
            );
        }

        if ($item instanceof Arrayable) {
            /** @var array<string, mixed> $payload */
            $payload = $item->toArray();

            return static::createFromInput($context, $payload);
        }

        if (is_array($item)) {
            /** @var array<string, mixed> $item */
            return static::createFromInput($context, $item);
        }

        if (is_object($item)) {
            if ($item instanceof stdClass) {
                return static::createFromInput($context, (array) $item);
            }

            /** @var array<string, mixed> $payload */
            $payload = get_object_vars($item);

            return static::createFromInput($context, $payload);
        }

        /** @var array<string, mixed> $payload */
        $payload = ['value' => $item];

        return static::createFromInput($context, $payload);
    }

    /**
     * @internal
     * @param  array<array-key, static>      $items
     * @return Collection<array-key, static>
     */
    protected static function eloquentCollectionFromMappedItems(array $items): Collection
    {
        /** @phpstan-ignore-next-line */
        return new EloquentCollection($items);
    }

    /**
     * @internal
     * @param array<array-key, mixed> $input
     */
    protected static function assertNoSuperfluousKeys(ClassMetadata $metadata, array $input): void
    {
        if (!$metadata->forbidSuperfluousKeys) {
            return;
        }

        foreach (array_keys($input) as $key) {
            if (array_key_exists($key, $metadata->inputNameLookup)) {
                continue;
            }

            throw SuperfluousInputKeyException::forKey($metadata->class, $key);
        }
    }

    /**
     * @internal
     * @param array<array-key, mixed> $input
     */
    protected static function resolveValue(ClassMetadata $metadata, PropertyMetadata $property, array $input, bool $cascadeValidation = false, ?CreationContext $context = null): mixed
    {
        [$hasValue, $value] = static::resolveInputValue($property, $input);

        if (!$hasValue) {
            if ($property->isOptional) {
                return Optional::missing();
            }

            if ($property->hasDefaultValue) {
                return $property->defaultValue;
            }

            if ($property->nullable && !$metadata->forbidUndefinedValues) {
                return null;
            }

            throw MissingDataValueException::forProperty($metadata->class, $property->name);
        }

        if ($property->replaceEmptyStringsWithNull && $property->nullable && is_string($value) && mb_trim($value) === '') {
            return null;
        }

        /** @var array<string, mixed> $rawInput */
        $rawInput = $input;

        return static::hydrateValue($property, $value, $cascadeValidation, $context, $rawInput);
    }

    /**
     * @internal
     * @param  array<array-key, mixed> $input
     * @return array{bool, mixed}
     */
    protected static function resolveInputValue(PropertyMetadata $property, array $input): array
    {
        if ($property->inputName === $property->name) {
            if (array_key_exists($property->name, $input)) {
                return [true, $input[$property->name]];
            }

            return [false, null];
        }

        if (array_key_exists($property->inputName, $input)) {
            return [true, $input[$property->inputName]];
        }

        if (array_key_exists($property->name, $input)) {
            return [true, $input[$property->name]];
        }

        return [false, null];
    }

    /**
     * @internal
     */
    protected static function generatedValueAttribute(
        ClassMetadata $metadata,
        PropertyMetadata $property,
        ?CreationContext $context = null,
    ): ?GeneratesMissingValueInterface {
        if (!$property->hasGeneratedValueAttribute) {
            return null;
        }

        $instances = [];

        foreach (static::propertyAttributes($property, $context) as $attribute) {
            if (!$attribute instanceof GeneratesMissingValueInterface) {
                continue;
            }

            $instances[] = $attribute;
        }

        if ($instances === []) {
            return null;
        }

        if ($property->isOptional) {
            throw InvalidGeneratedValueAttributeException::forOptionalProperty($metadata->class, $property->name);
        }

        if (!static::supportsGeneratedStringValue($property)) {
            throw InvalidGeneratedValueAttributeException::forUnsupportedPropertyType($metadata->class, $property->name);
        }

        if (($instances[1] ?? null) instanceof GeneratesMissingValueInterface) {
            throw InvalidGeneratedValueAttributeException::forMultipleAttributes($metadata->class, $property->name);
        }

        return $instances[0];
    }

    /**
     * @return list<object>
     */
    protected static function propertyAttributes(PropertyMetadata $property, ?CreationContext $context = null): array
    {
        if ($context instanceof CreationContext) {
            return $context->propertyAttributes($property);
        }

        $propertyAttributes = $property->property?->getAttributes() ?? [];

        if ($propertyAttributes !== []) {
            return array_map(
                static fn (ReflectionAttribute $attribute): object => $attribute->newInstance(),
                $propertyAttributes,
            );
        }

        return array_map(
            static fn (ReflectionAttribute $attribute): object => $attribute->newInstance(),
            $property->parameter->getAttributes(),
        );
    }

    protected static function supportsGeneratedStringValue(PropertyMetadata $property): bool
    {
        $types = [];

        foreach ($property->types as $type) {
            if ($type === 'null') {
                continue;
            }

            $types[] = $type;
        }

        return $types === ['string'];
    }

    /**
     * @internal
     * @param array<string, mixed> $values
     */
    protected static function resolveComputedValue(CreationContext $context, PropertyMetadata $property, array $values): mixed
    {
        if ($property->computer !== null) {
            $computer = $context->computer($property->computer);

            if ($computer instanceof ComputesValueInterface) {
                return $computer->compute(
                    static::computedInput($context->metadata, $values, $property),
                );
            }
        }

        if ($property->hasDefaultValue) {
            return $property->defaultValue;
        }

        return null;
    }

    /**
     * @internal
     * @param array<string, mixed> $values
     */
    protected static function resolveGeneratedCollectionValue(CreationContext $context, PropertyMetadata $property, array $values): mixed
    {
        $attribute = static::collectionSourceAttribute($property, $context);

        if ($attribute instanceof GeneratesCollectionValueInterface) {
            return $attribute->generateCollection($values, $context);
        }

        if ($property->hasDefaultValue) {
            return $property->defaultValue;
        }

        return null;
    }

    /**
     * @internal
     * @param array<string, mixed> $values
     */
    protected static function resolveCollectionResultValue(CreationContext $context, PropertyMetadata $property, array $values): mixed
    {
        $attribute = static::collectionResultAttribute($property, $context);

        if ($attribute instanceof ComputesCollectionResultValueInterface) {
            return $attribute->computeResult(
                static::collectionResultSource($property, $attribute, $values, $context),
                $values,
                $context,
            );
        }

        if ($property->hasDefaultValue) {
            return $property->defaultValue;
        }

        return null;
    }

    /**
     * @internal
     * @param array<string, mixed> $values
     */
    protected static function resolveHydratedLaravelCollectionValue(
        PropertyMetadata $property,
        array $values,
        ClassMetadata $metadata,
        ?CreationContext $context = null,
    ): mixed {
        $value = $values[$property->name] ?? null;

        if (!$value instanceof Collection) {
            return $value;
        }

        if ($property->laravelCollectionType === null && $property->laravelCollectionCastClass === null) {
            return $value;
        }

        return static::transformLaravelCollectionValue($property, $value, $metadata, $values, $context);
    }

    /**
     * @internal
     * @param array<string, mixed> $values
     */
    protected static function resolveHydratedLazyLaravelCollectionValue(
        PropertyMetadata $property,
        array $values,
        ClassMetadata $metadata,
        ?CreationContext $context = null,
    ): mixed {
        if (
            $property->lazyLaravelCollectionType === null
            && $property->lazyLaravelCollectionCastClass === null
            && !in_array(LazyCollection::class, $property->types, true)
        ) {
            return $values[$property->name] ?? null;
        }

        $value = $values[$property->name] ?? null;

        if ($value instanceof LazyCollection) {
            return static::transformLazyLaravelCollectionValue($property, $value, $metadata, $values, $context);
        }

        if (!is_iterable($value)) {
            return $value;
        }

        $lazy = static::hydrateLazyLaravelCollectionItems($property, $value, false, $context);

        return static::transformLazyLaravelCollectionValue($property, $lazy, $metadata, $values, $context);
    }

    /**
     * @internal
     */
    protected static function collectionSourceAttribute(PropertyMetadata $property, ?CreationContext $context = null): ?GeneratesCollectionValueInterface
    {
        foreach (static::propertyAttributes($property, $context) as $attribute) {
            if ($attribute instanceof GeneratesCollectionValueInterface) {
                return $attribute;
            }
        }

        return null;
    }

    /**
     * @internal
     */
    protected static function collectionResultAttribute(PropertyMetadata $property, ?CreationContext $context = null): ?ComputesCollectionResultValueInterface
    {
        foreach (static::propertyAttributes($property, $context) as $attribute) {
            if ($attribute instanceof ComputesCollectionResultValueInterface) {
                return $attribute;
            }
        }

        return null;
    }

    /**
     * @internal
     * @param  array<string, mixed>         $values
     * @return Collection<array-key, mixed>
     */
    protected static function collectionResultSource(
        PropertyMetadata $property,
        ComputesCollectionResultValueInterface $attribute,
        array $values,
        ?CreationContext $context = null,
    ): Collection {
        $sourceProperty = $attribute->sourceProperty();
        $source = $values[$sourceProperty] ?? null;

        if ($source instanceof Collection) {
            return $source;
        }

        if ($source instanceof LazyCollection) {
            if ($context instanceof CreationContext) {
                return $context->materializedCollectionSource(
                    $sourceProperty,
                    static fn (): Collection => $source->collect(),
                );
            }

            return $source->collect();
        }

        throw InvalidCollectionAttributeException::forMissingSourceProperty($property->name, $sourceProperty);
    }

    /**
     * @internal
     * @param  array<string, mixed> $values
     * @return array<string, mixed>
     */
    protected static function computedInput(ClassMetadata $metadata, array $values, PropertyMetadata $property): array
    {
        $input = [];

        foreach ($metadata->computedInputNamesFor($property->name) as $name) {
            if (!array_key_exists($name, $values)) {
                continue;
            }

            $input[$name] = $values[$name];
        }

        return $input;
    }

    /**
     * @internal
     * @param array<string, mixed> $rawInput
     */
    protected static function hydrateValue(
        PropertyMetadata $property,
        mixed $value,
        bool $cascadeValidation = false,
        ?CreationContext $context = null,
        array $rawInput = [],
    ): mixed {
        if ($value instanceof Optional) {
            return $value;
        }

        $metadata = $context instanceof CreationContext ? $context->metadata : static::metadata();

        static::assertLaravelCollectionCallbackAttributesSupported($property, $metadata, $context);
        static::assertLazyLaravelCollectionAttributesSupported($property, $metadata, $context);
        static::assertLazyCollectionAttributesUnsupported($property, $metadata, $context);

        if ($property->cast instanceof ContextualCastInterface) {
            return $property->cast->getWithContext(
                $property,
                $value,
                static::propertyHydrationContext($property, $rawInput, $context),
            );
        }

        if ($property->cast instanceof CastInterface) {
            return $property->cast->get($property, $value);
        }

        if ($property->dataListType !== null) {
            return new DataList(static::transformCollectionValue(
                $property,
                static::hydrateCollectionItems($property, $value, true, $cascadeValidation, $context, $rawInput),
                true,
                $metadata,
                $context,
                $rawInput,
            ));
        }

        if ($property->dataListCastClass !== null) {
            return new DataList(static::transformCollectionValue(
                $property,
                static::hydrateCollectionItems($property, $value, true, $cascadeValidation, $context, $rawInput),
                true,
                $metadata,
                $context,
                $rawInput,
            ));
        }

        if ($property->dataCollectionType !== null || $property->dataCollectionCastClass !== null) {
            return new DataCollection(static::transformCollectionValue(
                $property,
                static::hydrateCollectionItems($property, $value, false, $cascadeValidation, $context, $rawInput),
                false,
                $metadata,
                $context,
                $rawInput,
            ));
        }

        if ($property->lazyDataListType !== null || $property->lazyDataListCastClass !== null) {
            return new LazyDataList(
                static::collectionInputIterable($value),
                static::lazyCollectionHydrator($property, $cascadeValidation, $context, $rawInput),
            );
        }

        if ($property->lazyDataCollectionType !== null || $property->lazyDataCollectionCastClass !== null) {
            return new LazyDataCollection(
                static::collectionInputIterable($value),
                static::lazyCollectionHydrator($property, $cascadeValidation, $context, $rawInput),
            );
        }

        if ($property->laravelCollectionType !== null || $property->laravelCollectionCastClass !== null) {
            return static::hydrateLaravelCollectionItems($property, $value, $cascadeValidation, $context, $rawInput);
        }

        if ($property->lazyLaravelCollectionType !== null || $property->lazyLaravelCollectionCastClass !== null) {
            return static::hydrateLazyLaravelCollectionItems($property, $value, $cascadeValidation, $context, $rawInput);
        }

        foreach ($property->types as $index => $type) {
            if ($type === 'null') {
                continue;
            }

            if ($type === Optional::class) {
                continue;
            }

            $hydrated = static::hydrateTypedValue(
                $type,
                $property->typeKinds[$index] ?? 'other',
                $value,
                $cascadeValidation,
                $context,
            );

            if (($property->typeKinds[$index] ?? 'other') === 'array') {
                /** @var array<array-key, mixed> $hydrated */
                return static::transformCollectionValue(
                    $property,
                    $hydrated,
                    false,
                    $metadata,
                    $context,
                    $rawInput,
                );
            }

            return $hydrated;
        }

        return $value;
    }

    /**
     * @internal
     * @param  array<array-key, mixed> $items
     * @param  array<string, mixed>    $rawInput
     * @return array<array-key, mixed>
     */
    protected static function transformCollectionValue(
        PropertyMetadata $property,
        array $items,
        bool $isList,
        ClassMetadata $metadata,
        ?CreationContext $context = null,
        array $rawInput = [],
    ): array {
        $attributes = static::collectionAttributes($property, $metadata, $isList, $context);

        foreach ($attributes as $attribute) {
            if ($attribute instanceof ContextualTransformsCollectionValueInterface) {
                $items = $attribute->transformWithContext(
                    $items,
                    static::propertyHydrationContext($property, $rawInput, $context),
                );

                continue;
            }

            $items = $attribute->transform($items);
        }

        return $items;
    }

    /**
     * @internal
     * @return list<TransformsCollectionValueInterface>
     */
    protected static function collectionAttributes(
        PropertyMetadata $property,
        ClassMetadata $metadata,
        bool $isList,
        ?CreationContext $context = null,
    ): array {
        if (!$property->hasCollectionTransformAttribute) {
            return [];
        }

        $instances = [];

        foreach (static::propertyAttributes($property, $context) as $attribute) {
            if (!$attribute instanceof TransformsCollectionValueInterface) {
                continue;
            }

            if (!$attribute instanceof AbstractCollectionTransformer) {
                continue;
            }

            if ($isList && !$attribute->supportsLists()) {
                throw InvalidCollectionAttributeException::forUnsupportedListAttribute(
                    $metadata->class,
                    $property->name,
                    $attribute::class,
                );
            }

            $instances[] = $attribute;
        }

        if ($instances === []) {
            return [];
        }

        if (
            $property->lazyDataListType !== null
            || $property->lazyDataListCastClass !== null
            || $property->lazyDataCollectionType !== null
            || $property->lazyDataCollectionCastClass !== null
            || in_array(LazyDataList::class, $property->types, true)
            || in_array(LazyDataCollection::class, $property->types, true)
        ) {
            throw InvalidCollectionAttributeException::forLazyCollectionTransform(
                $metadata->class,
                $property->name,
            );
        }

        if (
            !$isList
            && $property->dataCollectionType === null
            && $property->dataCollectionCastClass === null
            && $property->laravelCollectionType === null
            && $property->laravelCollectionCastClass === null
            && !in_array('array', $property->types, true)
        ) {
            throw InvalidCollectionAttributeException::forUnsupportedPropertyType($metadata->class, $property->name);
        }

        return $instances;
    }

    /**
     * @internal
     * @param  Collection<array-key, mixed> $items
     * @param  array<string, mixed>         $properties
     * @return Collection<array-key, mixed>
     */
    protected static function transformLaravelCollectionValue(
        PropertyMetadata $property,
        Collection $items,
        ClassMetadata $metadata,
        array $properties = [],
        ?CreationContext $context = null,
    ): Collection {
        $attributes = static::laravelCollectionAttributes($property, $metadata, $context);

        foreach ($attributes as $attribute) {
            if ($context instanceof CreationContext) {
                $context->setProperties($properties);
            }

            $items = $attribute->transformCollection($items, $context);
        }

        return $items;
    }

    /**
     * @internal
     * @param  LazyCollection<array-key, mixed> $items
     * @param  array<string, mixed>             $properties
     * @return LazyCollection<array-key, mixed>
     */
    protected static function transformLazyLaravelCollectionValue(
        PropertyMetadata $property,
        LazyCollection $items,
        ClassMetadata $metadata,
        array $properties = [],
        ?CreationContext $context = null,
    ): LazyCollection {
        $attributes = static::lazyLaravelCollectionAttributes($property, $metadata, $context);

        foreach ($attributes as $attribute) {
            if ($context instanceof CreationContext) {
                $context->setProperties($properties);
            }

            $items = $attribute->transformLazyCollection($items, $context);
        }

        return $items;
    }

    /**
     * @internal
     * @return list<TransformsLaravelCollectionValueInterface>
     */
    protected static function laravelCollectionAttributes(
        PropertyMetadata $property,
        ClassMetadata $metadata,
        ?CreationContext $context = null,
    ): array {
        if (!$property->hasLaravelCollectionTransformAttribute) {
            return [];
        }

        $instances = [];

        foreach (static::propertyAttributes($property, $context) as $attribute) {
            if (!$attribute instanceof TransformsLaravelCollectionValueInterface) {
                if (!$attribute instanceof WrapsLaravelCollectionTransformInterface) {
                    continue;
                }

                $instances[] = $attribute;

                continue;
            }

            $instances[] = $attribute;
        }

        if ($instances === []) {
            return [];
        }

        if (
            $property->laravelCollectionType === null
            && $property->laravelCollectionCastClass === null
            && !in_array(Collection::class, $property->types, true)
        ) {
            throw InvalidCollectionAttributeException::forUnsupportedPropertyType($metadata->class, $property->name);
        }

        $transforms = [];

        foreach ($instances as $index => $instance) {
            if ($instance instanceof WrapsLaravelCollectionTransformInterface) {
                $next = $instances[$index + 1] ?? null;

                if (!$next instanceof TransformsLaravelCollectionValueInterface) {
                    throw InvalidCollectionAttributeException::forMissingFollowingTransform(
                        $metadata->class,
                        $property->name,
                        $instance::class,
                    );
                }

                $transforms[] = $instance->wrap($next);

                continue;
            }

            if (($instances[$index - 1] ?? null) instanceof WrapsLaravelCollectionTransformInterface) {
                continue;
            }

            $transforms[] = $instance;
        }

        return $transforms;
    }

    /**
     * @internal
     * @return list<TransformsLazyCollectionValueInterface>
     */
    protected static function lazyLaravelCollectionAttributes(
        PropertyMetadata $property,
        ClassMetadata $metadata,
        ?CreationContext $context = null,
    ): array {
        if (!$property->hasLazyLaravelCollectionTransformAttribute) {
            return [];
        }

        $instances = [];

        foreach (static::propertyAttributes($property, $context) as $attribute) {
            if (!$attribute instanceof TransformsLazyCollectionValueInterface) {
                continue;
            }

            $instances[] = $attribute;
        }

        if ($instances === []) {
            return [];
        }

        if (
            $property->lazyLaravelCollectionType === null
            && $property->lazyLaravelCollectionCastClass === null
            && !in_array(LazyCollection::class, $property->types, true)
        ) {
            throw InvalidCollectionAttributeException::forUnsupportedPropertyType($metadata->class, $property->name);
        }

        return $instances;
    }

    /**
     * @internal
     */
    protected static function assertLaravelCollectionCallbackAttributesSupported(
        PropertyMetadata $property,
        ClassMetadata $metadata,
        ?CreationContext $context = null,
    ): void {
        if (!$property->hasLaravelCollectionTransformAttribute) {
            return;
        }

        if (
            $property->laravelCollectionType !== null
            || $property->laravelCollectionCastClass !== null
            || in_array(Collection::class, $property->types, true)
            || $property->lazyLaravelCollectionType !== null
            || $property->lazyLaravelCollectionCastClass !== null
            || in_array(LazyCollection::class, $property->types, true)
        ) {
            return;
        }

        foreach (static::propertyAttributes($property, $context) as $attribute) {
            if (!$attribute instanceof TransformsLaravelCollectionValueInterface) {
                continue;
            }

            if ($attribute instanceof AbstractCollectionTransformer) {
                continue;
            }

            throw InvalidCollectionAttributeException::forLaravelCollectionOnly(
                $metadata->class,
                $property->name,
                $attribute::class,
            );
        }
    }

    /**
     * @internal
     */
    protected static function assertLazyLaravelCollectionAttributesSupported(
        PropertyMetadata $property,
        ClassMetadata $metadata,
        ?CreationContext $context = null,
    ): void {
        if (!$property->hasLazyLaravelCollectionTransformAttribute && !$property->hasCollectionTransformAttribute) {
            return;
        }

        if (
            $property->lazyLaravelCollectionType === null
            && $property->lazyLaravelCollectionCastClass === null
            && !in_array(LazyCollection::class, $property->types, true)
        ) {
            return;
        }

        foreach (static::propertyAttributes($property, $context) as $attribute) {
            if ($attribute instanceof TransformsLazyCollectionValueInterface) {
                continue;
            }

            if ($attribute instanceof TransformsCollectionValueInterface) {
                throw InvalidCollectionAttributeException::forLazyLaravelCollectionOnly(
                    $metadata->class,
                    $property->name,
                    $attribute::class,
                );
            }

            if ($attribute instanceof TransformsLaravelCollectionValueInterface) {
                throw InvalidCollectionAttributeException::forLazyLaravelCollectionOnly(
                    $metadata->class,
                    $property->name,
                    $attribute::class,
                );
            }
        }
    }

    /**
     * @internal
     */
    protected static function assertLazyCollectionAttributesUnsupported(
        PropertyMetadata $property,
        ClassMetadata $metadata,
        ?CreationContext $context = null,
    ): void {
        if (!$property->hasCollectionTransformAttribute) {
            return;
        }

        if (
            $property->lazyDataListType === null
            && $property->lazyDataListCastClass === null
            && $property->lazyDataCollectionType === null
            && $property->lazyDataCollectionCastClass === null
            && !in_array(LazyDataList::class, $property->types, true)
            && !in_array(LazyDataCollection::class, $property->types, true)
        ) {
            return;
        }

        foreach (static::propertyAttributes($property, $context) as $attribute) {
            if (!$attribute instanceof TransformsCollectionValueInterface) {
                continue;
            }

            throw InvalidCollectionAttributeException::forLazyCollectionTransform(
                $metadata->class,
                $property->name,
            );
        }
    }

    /**
     * @internal
     * @param array<array-key, mixed> $input
     */
    protected static function assertNoRecursiveInput(
        array $input,
        ?CreationContext $context = null,
    ): void {
        $guard = $context?->hydrationGuard() ?? new HydrationGuard();
        $guard->assertNoRecursion($input);
    }

    /**
     * @internal
     */
    protected static function hydrateType(string $type, mixed $value, bool $cascadeValidation = false): mixed
    {
        return static::hydrateTypedValue(
            $type,
            PropertyMetadata::typeKind($type),
            $value,
            $cascadeValidation,
        );
    }

    /**
     * @internal
     */
    protected static function hydrateTypedValue(
        string $type,
        string $typeKind,
        mixed $value,
        bool $cascadeValidation = false,
        ?CreationContext $context = null,
    ): mixed {
        if ($value === null) {
            return null;
        }

        return match ($typeKind) {
            'mixed' => $value,
            'array' => (array) $value,
            'bool' => (bool) $value,
            'float' => is_numeric($value) ? (float) $value : $value,
            'int' => is_numeric($value) ? (int) $value : $value,
            'string' => is_string($value) || is_numeric($value) || $value instanceof Stringable
                ? (string) $value
                : $value,
            'backed-enum' => $value instanceof $type
                ? $value
                : ((!is_string($value) && !is_int($value)) ? $value : $type::from($value)),
            'unit-enum' => $value instanceof $type
                ? $value
                : (is_string($value) ? constant($type.'::'.$value) : $value),
            'data' => $value instanceof $type
                ? $value
                : (is_array($value)
                    ? static::hydrateNestedDataObject(
                        $type,
                        $value,
                        $cascadeValidation,
                        $context,
                    )
                    : $value),
            'datetime' => (!is_string($value) && !$value instanceof Stringable)
                ? $value
                : new $type((string) $value),
            default => $value,
        };
    }

    /**
     * @internal
     * @param  array<string, mixed>    $rawInput
     * @return array<array-key, mixed>
     */
    protected static function hydrateCollectionItems(
        PropertyMetadata $property,
        mixed $value,
        bool $normalizeKeys,
        bool $cascadeValidation = false,
        ?CreationContext $context = null,
        array $rawInput = [],
    ): array {
        if (!is_iterable($value)) {
            return [];
        }

        if (!static::propertyHasCollectionItemCast($property)) {
            $items = [];

            foreach ($value as $key => $item) {
                if (!is_int($key) && !is_string($key)) {
                    continue;
                }

                $items[$key] = static::hydrateCollectionItemFromProperty(
                    $property,
                    $item,
                    $cascadeValidation,
                    $context,
                    $rawInput,
                );
            }

            return $normalizeKeys ? array_values($items) : $items;
        }

        $itemRuntime = $context?->collectionItem($property)
            ?? new CollectionItemRuntime($property, $property->collectionItemDescriptor());
        $items = [];

        foreach ($value as $key => $item) {
            if (!is_int($key) && !is_string($key)) {
                continue;
            }

            $items[$key] = static::hydrateCollectionItemValue(
                $itemRuntime,
                $item,
                $cascadeValidation,
                $context,
                $rawInput,
            );
        }

        if ($normalizeKeys) {
            return array_values($items);
        }

        return $items;
    }

    /**
     * @internal
     * @param  array<string, mixed>         $rawInput
     * @return Collection<array-key, mixed>
     */
    protected static function hydrateLaravelCollectionItems(
        PropertyMetadata $property,
        mixed $value,
        bool $cascadeValidation = false,
        ?CreationContext $context = null,
        array $rawInput = [],
    ): Collection {
        if (!is_iterable($value)) {
            return new Collection();
        }

        if (!static::propertyHasCollectionItemCast($property)) {
            $items = [];

            foreach ($value as $key => $item) {
                if (!is_int($key) && !is_string($key)) {
                    continue;
                }

                $items[$key] = static::hydrateCollectionItemFromProperty(
                    $property,
                    $item,
                    $cascadeValidation,
                    $context,
                    $rawInput,
                );
            }

            return new Collection($items);
        }

        $itemRuntime = $context?->collectionItem($property)
            ?? new CollectionItemRuntime($property, $property->collectionItemDescriptor());
        $items = [];

        foreach ($value as $key => $item) {
            if (!is_int($key) && !is_string($key)) {
                continue;
            }

            $items[$key] = static::hydrateCollectionItemValue(
                $itemRuntime,
                $item,
                $cascadeValidation,
                $context,
                $rawInput,
            );
        }

        return new Collection($items);
    }

    /**
     * @internal
     * @param  array<string, mixed>             $rawInput
     * @return LazyCollection<array-key, mixed>
     */
    protected static function hydrateLazyLaravelCollectionItems(
        PropertyMetadata $property,
        mixed $value,
        bool $cascadeValidation = false,
        ?CreationContext $context = null,
        array $rawInput = [],
    ): LazyCollection {
        /** @var LazyCollectionState<array-key, mixed> $state */
        $state = new LazyCollectionState(
            static::collectionInputIterable($value),
            static::lazyCollectionHydrator($property, $cascadeValidation, $context, $rawInput),
            false,
        );

        return LazyCollection::make(
            static function () use ($state): Traversable {
                yield from $state->iterate();
            },
        );
    }

    /**
     * @internal
     * @return iterable<array-key, mixed>
     */
    protected static function collectionInputIterable(mixed $value): iterable
    {
        if (is_array($value)) {
            return $value;
        }

        if ($value instanceof Traversable) {
            return $value;
        }

        return new EmptyIterator();
    }

    /**
     * @internal
     * @param  array<string, mixed>              $rawInput
     * @return Closure(int|string, mixed): mixed
     */
    protected static function lazyCollectionHydrator(
        PropertyMetadata $property,
        bool $cascadeValidation = false,
        ?CreationContext $context = null,
        array $rawInput = [],
    ): Closure {
        if (!static::propertyHasCollectionItemCast($property)) {
            return static fn (int|string $key, mixed $value): mixed => static::hydrateCollectionItemFromProperty(
                $property,
                $value,
                $cascadeValidation,
                $context,
                $rawInput,
            );
        }

        $itemRuntime = $context?->collectionItem($property)
            ?? new CollectionItemRuntime($property, $property->collectionItemDescriptor());

        return static fn (int|string $key, mixed $value): mixed => static::hydrateCollectionItemValue(
            $itemRuntime,
            $value,
            $cascadeValidation,
            $context,
            $rawInput,
        );
    }

    /**
     * @internal
     * @param array<string, mixed> $rawInput
     */
    protected static function hydrateCollectionItem(
        PropertyMetadata $property,
        mixed $value,
        bool $cascadeValidation = false,
        ?CreationContext $context = null,
        array $rawInput = [],
    ): mixed {
        if (!static::propertyHasCollectionItemCast($property)) {
            return static::hydrateCollectionItemFromProperty(
                $property,
                $value,
                $cascadeValidation,
                $context,
                $rawInput,
            );
        }

        $itemRuntime = $context?->collectionItem($property)
            ?? new CollectionItemRuntime($property, $property->collectionItemDescriptor());

        return static::hydrateCollectionItemValue(
            $itemRuntime,
            $value,
            $cascadeValidation,
            $context,
            $rawInput,
        );
    }

    /**
     * @internal
     * @param array<string, mixed> $rawInput
     */
    protected static function hydrateCollectionItemValue(
        CollectionItemRuntime $item,
        mixed $value,
        bool $cascadeValidation = false,
        ?CreationContext $context = null,
        array $rawInput = [],
    ): mixed {
        $cast = $item->cast();

        if ($cast instanceof ContextualCastInterface) {
            return $cast->getWithContext(
                $item->property(),
                $value,
                static::propertyHydrationContext($item->property(), $rawInput, $context),
            );
        }

        if ($cast instanceof CastInterface) {
            return $cast->get($item->property(), $value);
        }

        foreach ($item->types() as $index => $type) {
            if ($type === 'null') {
                continue;
            }

            if ($type === Optional::class) {
                continue;
            }

            return static::hydrateTypedValue(
                $type,
                $item->typeKindAt($index),
                $value,
                $cascadeValidation,
                $context,
            );
        }

        return $value;
    }

    /**
     * @internal
     * @param array<string, mixed> $rawInput
     */
    protected static function hydrateCollectionItemFromProperty(
        PropertyMetadata $property,
        mixed $value,
        bool $cascadeValidation = false,
        ?CreationContext $context = null,
        array $rawInput = [],
    ): mixed {
        $type = $property->collectionItemType();

        if ($type === null) {
            return $value;
        }

        return static::hydrateTypedValue(
            $type,
            $property->collectionItemTypeKind() ?? 'other',
            $value,
            $cascadeValidation,
            $context,
        );
    }

    /**
     * @internal
     * @param array<string, mixed> $rawInput
     */
    protected static function propertyHydrationContext(
        PropertyMetadata $property,
        array $rawInput = [],
        ?CreationContext $context = null,
    ): PropertyHydrationContext {
        if ($context instanceof CreationContext) {
            return $context->propertyHydrationContext($property);
        }

        return new PropertyHydrationContext(
            dataClass: static::class,
            property: $property,
            rawInput: $rawInput,
            resolvedProperties: [],
        );
    }

    /**
     * @internal
     * @param array<array-key, mixed> $value
     */
    protected static function hydrateNestedDataObject(
        string $type,
        array $value,
        bool $cascadeValidation = false,
        ?CreationContext $context = null,
    ): mixed {
        if (!is_a($type, self::class, true)) {
            return $value;
        }

        /** @var class-string<self> $type */
        /** @var array<string, mixed> $payload */
        $payload = $value;

        if (!$context instanceof CreationContext) {
            return $cascadeValidation
                ? $type::createWithValidation($payload)
                : $type::create($payload);
        }

        $childContext = $context->child($type);

        return $cascadeValidation
            ? $type::createFromInputWithValidation($childContext, $payload)
            : $type::createFromInput($childContext, $payload);
    }

    /**
     * @internal
     */
    protected static function collectionItemProperty(
        PropertyMetadata $property,
        ?CreationContext $context = null,
        ?SerializationContext $serializationContext = null,
    ): PropertyMetadata {
        if ($context instanceof CreationContext) {
            return $context->collectionItem($property)->property();
        }

        if ($serializationContext instanceof SerializationContext) {
            return $serializationContext->collectionItem($property)->property();
        }

        return $property->forCollectionItem();
    }

    /**
     * @internal
     */
    protected static function serializeObject(
        object $object,
        SerializationContext $context,
    ): mixed {
        $context->guard->enter($object);

        try {
            if ($object instanceof stdClass) {
                /** @var array<string, mixed> $values */
                $values = (array) $object;

                foreach ($values as $key => $value) {
                    $values[$key] = static::serializeAny($value, $context);
                }

                return $values;
            }

            $values = [];

            foreach (get_object_vars($object) as $key => $value) {
                $values[$key] = static::serializeAny($value, $context);
            }

            return $values;
        } finally {
            $context->guard->leave($object);
        }
    }

    /**
     * @internal
     */
    protected static function serializeAny(
        mixed $value,
        SerializationContext $context,
        ?PropertyMetadata $property = null,
    ): mixed {
        if ($value instanceof Optional) {
            return null;
        }

        if ($property?->cast instanceof CastInterface) {
            return $property->cast->set($property, $value);
        }

        if ($value instanceof self) {
            return $value->toArrayUsingContext($context);
        }

        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof UnitEnum) {
            return $value->name;
        }

        if ($value instanceof DateTimeInterface) {
            $date = $context->options->date;

            if ($date->timezone === null) {
                return $value->format($date->format);
            }

            return DateFormat::formatWithTimezone(
                $value,
                $date->format,
                $date->timezone,
            );
        }

        if (
            $value instanceof DataList
            || $value instanceof DataCollection
            || $value instanceof LazyDataList
            || $value instanceof LazyDataCollection
        ) {
            return static::serializeCollectionItems($property, $value, $context);
        }

        if ($value instanceof Collection || $value instanceof LazyCollection) {
            return static::serializeLaravelCollectionItems($property, $value, $context);
        }

        if ($value instanceof Arrayable) {
            return static::serializeAny($value->toArray(), $context, $property);
        }

        if (is_array($value)) {
            $classification = static::classifyArrayValue($value);

            if ($classification['kind'] === 'plain') {
                return $classification['plain'] ?? [];
            }

            if ($classification['kind'] === 'data') {
                return static::serializeDataObjectArray($value, $context);
            }

            $items = [];

            foreach ($value as $key => $item) {
                $items[$key] = static::serializeAny($item, $context);
            }

            return $items;
        }

        if (is_object($value)) {
            return static::serializeObject($value, $context);
        }

        return $value;
    }

    /**
     * @internal
     * @param  array<array-key, mixed> $values
     * @return array<array-key, mixed>
     */
    protected static function serializeDataObjectArray(array $values, SerializationContext $context): array
    {
        $serialized = [];

        foreach ($values as $key => $value) {
            $serialized[$key] = $value instanceof self
                ? $value->toArrayUsingContext($context)
                : static::serializeAny($value, $context);
        }

        return $serialized;
    }

    /**
     * @internal
     * @param  array<array-key, mixed>                                                  $values
     * @return array{kind: 'data'|'mixed'|'plain', plain: null|array<array-key, mixed>}
     */
    protected static function classifyArrayValue(array $values): array
    {
        $plain = [];
        $onlyPlainValues = true;
        $onlyDataObjects = true;

        foreach ($values as $key => $value) {
            if ($onlyPlainValues) {
                if (self::normalizePlainArrayValue($value, $normalized)) {
                    $plain[$key] = $normalized;
                } else {
                    $onlyPlainValues = false;
                    $plain = [];
                }
            }

            if ($onlyDataObjects && !$value instanceof self) {
                $onlyDataObjects = false;
            }

            if (!$onlyPlainValues && !$onlyDataObjects) {
                return ['kind' => 'mixed', 'plain' => null];
            }
        }

        if ($onlyPlainValues) {
            return ['kind' => 'plain', 'plain' => $plain];
        }

        if ($onlyDataObjects) {
            return ['kind' => 'data', 'plain' => null];
        }

        return ['kind' => 'mixed', 'plain' => null];
    }

    /**
     * @internal
     * @param array<array-key, mixed> $values
     */
    protected static function containsOnlyDataObjects(array $values): bool
    {
        return static::classifyArrayValue($values)['kind'] === 'data' && $values !== [];
    }

    /**
     * @internal
     * @param array<array-key, mixed> $value
     */
    protected static function isPlainArrayValue(array $value): bool
    {
        return static::classifyArrayValue($value)['kind'] === 'plain';
    }

    /**
     * @internal
     * @param  array<array-key, mixed> $value
     * @return array<array-key, mixed>
     */
    protected static function plainArrayValue(array $value): array
    {
        return static::classifyArrayValue($value)['plain'] ?? [];
    }

    /**
     * @internal
     * @param  DataCollection<array-key, mixed>|DataList<mixed>|LazyDataCollection<array-key, mixed>|LazyDataList<mixed> $value
     * @return array<array-key, mixed>
     */
    protected static function serializeCollectionItems(
        ?PropertyMetadata $property,
        DataList|DataCollection|LazyDataList|LazyDataCollection $value,
        SerializationContext $context,
    ): array {
        if ($property instanceof PropertyMetadata) {
            if (!static::propertyHasCollectionItemCast($property)) {
                return $value->toArrayUsingContext($context);
            }

            $itemRuntime = $context->collectionItem($property);
        } else {
            $itemRuntime = null;
        }

        $normalizeKeys = $value instanceof DataList || $value instanceof LazyDataList;
        $items = [];

        if ($normalizeKeys) {
            foreach ($value as $item) {
                $items[] = static::serializeCollectionItemValue(
                    $itemRuntime,
                    $item,
                    $context,
                );
            }

            return $items;
        }

        foreach ($value as $key => $item) {
            $items[$key] = static::serializeCollectionItemValue(
                $itemRuntime,
                $item,
                $context,
            );
        }

        return $items;
    }

    /**
     * @internal
     * @param  Collection<array-key, mixed>|LazyCollection<array-key, mixed> $value
     * @return array<array-key, mixed>
     */
    protected static function serializeLaravelCollectionItems(
        ?PropertyMetadata $property,
        Collection|LazyCollection $value,
        SerializationContext $context,
    ): array {
        $items = [];
        $itemRuntime = $property instanceof PropertyMetadata && static::propertyHasCollectionItemCast($property)
            ? $context->collectionItem($property)
            : null;

        foreach ($value as $key => $item) {
            $items[$key] = static::serializeCollectionItemValue(
                $itemRuntime,
                $item,
                $context,
            );
        }

        return $items;
    }

    /**
     * @internal
     */
    protected static function serializeCollectionItem(
        ?PropertyMetadata $property,
        mixed $value,
        SerializationContext $context,
    ): mixed {
        $itemRuntime = $property instanceof PropertyMetadata && static::propertyHasCollectionItemCast($property)
            ? $context->collectionItem($property)
            : null;

        return static::serializeCollectionItemValue(
            $itemRuntime,
            $value,
            $context,
        );
    }

    /**
     * @internal
     */
    protected static function serializeCollectionItemValue(
        ?CollectionItemRuntime $itemRuntime,
        mixed $value,
        SerializationContext $context,
    ): mixed {
        $cast = $itemRuntime?->cast();

        if ($cast instanceof CastInterface) {
            $itemProperty = $itemRuntime->property();

            return $cast->set($itemProperty, $value);
        }

        return static::serializeAny($value, $context);
    }

    /**
     * @internal
     */
    protected static function propertyHasCollectionItemCast(PropertyMetadata $property): bool
    {
        if ($property->hasCollectionItemCast) {
            return true;
        }

        if ($property->collectionItemCast() instanceof CastInterface) {
            return true;
        }

        if (
            $property->dataListCastClass !== null
            || $property->dataCollectionCastClass !== null
            || $property->lazyDataListCastClass !== null
            || $property->lazyDataCollectionCastClass !== null
            || $property->laravelCollectionCastClass !== null
            || $property->lazyLaravelCollectionCastClass !== null
        ) {
            return true;
        }

        $descriptor = $property->collectionItemDescriptor();

        return $descriptor?->cast instanceof CastInterface || $descriptor?->castClass !== null;
    }

    /**
     * @internal
     * @return array<string, mixed>
     */
    protected function serializePayload(SerializationContext $context): array
    {
        $context->guard->enter($this);

        try {
            $metadata = $context->metadataFor(static::class, static fn (): ClassMetadata => static::metadata());
            $useScopedChildContexts = $context->options->hasScopedPaths();

            $payload = [];
            $plan = $context->projectionPlanFor($metadata);

            foreach ($plan->entries as $entry) {
                $property = $entry->property;

                if ($entry->conditional && !$this->shouldSerializeProperty($property, $context)) {
                    continue;
                }

                $propertyContext = $useScopedChildContexts
                    ? $context->child($property->outputName)
                    : $context;
                $value = $entry->derived
                    ? $this->serializationValue($metadata, $property, $context)
                    : $this->{$property->name};

                $payload[$property->outputName] = static::serializeAny(
                    $value,
                    $propertyContext,
                    $property,
                );
            }

            return $payload;
        } finally {
            $context->guard->leave($this);
        }
    }

    /**
     * Fast path for the default serializer configuration where no
     * projection planning or scoped child resolution is required.
     *
     * @internal
     * @return array<string, mixed>
     */
    protected function serializeDefaultPayloadUsingContext(
        SerializationContext $context,
    ): array {
        $context->guard->enter($this);

        try {
            $metadata = $context->metadataFor(static::class, static fn (): ClassMetadata => static::metadata());

            return $this->serializeDefaultPayload($metadata, $context, $context->options);
        } finally {
            $context->guard->leave($this);
        }
    }

    /**
     * Fast path for the default serializer configuration where no nested
     * projection state needs to be derived per property.
     *
     * @internal
     * @return array<string, mixed>
     */
    protected function serializeDefaultPayload(
        ClassMetadata $metadata,
        SerializationContext $context,
        SerializationOptions $options,
    ): array {
        $payload = [];
        $properties = $options->includeSensitive
            ? $metadata->defaultProjectionProperties
            : $metadata->defaultProjectionPropertiesWithoutSensitive;

        foreach ($properties as $property) {
            $payload[$property->outputName] = static::serializeAny(
                $this->{$property->name},
                $context,
                $property,
            );
        }

        return $payload;
    }

    /**
     * @internal
     */
    protected function shouldSerializeProperty(
        PropertyMetadata $property,
        SerializationContext $context,
    ): bool {
        if (!$context->options->includeSensitive && $property->isSensitive) {
            return false;
        }

        if ($context->options->shouldExcludePath($property->outputName)) {
            return false;
        }

        foreach ($property->excludeConditions as $conditionClass) {
            if ($this->matchesCondition($conditionClass, $property, $context)) {
                return false;
            }
        }

        if (!$property->isLazy) {
            return true;
        }

        if ($context->options->shouldIncludePath($property->outputName)) {
            return true;
        }

        if ($context->options->includesGroup($property->lazyGroups)) {
            return true;
        }

        foreach ($property->includeConditions as $conditionClass) {
            if ($this->matchesCondition($conditionClass, $property, $context)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @internal
     */
    protected function matchesCondition(
        string $conditionClass,
        PropertyMetadata $property,
        SerializationContext $context,
    ): bool {
        $condition = $context->resolveSerializable($conditionClass);

        if (!$condition instanceof SerializationConditionInterface) {
            return false;
        }

        return $condition->shouldInclude($this, $property, $context->options);
    }

    /**
     * @internal
     */
    protected function serializationValue(
        ClassMetadata $metadata,
        PropertyMetadata $property,
        SerializationContext $context,
    ): mixed {
        if ($property->lazyResolver !== null) {
            return $this->resolveLazyValue($metadata, $property, $context);
        }

        if ($property->isComputed && $property->isLazy) {
            return $this->computePropertyValue($metadata, $property, $context);
        }

        return $this->{$property->name};
    }

    /**
     * @internal
     */
    protected function resolveLazyValue(
        ClassMetadata $metadata,
        PropertyMetadata $property,
        SerializationContext $context,
    ): mixed {
        $resolver = $context->resolveSerializable($property->lazyResolver);

        if ($resolver instanceof ResolvesLazyValueInterface) {
            return $resolver->resolve($this, $property, $context->options);
        }

        if ($resolver instanceof ComputesValueInterface) {
            return $resolver->compute($this->computedInputFromObject($metadata, $property, $context));
        }

        return $this->{$property->name};
    }

    /**
     * @internal
     * @return array<string, mixed>
     */
    protected function computedInputFromObject(
        ClassMetadata $metadata,
        PropertyMetadata $property,
        ?SerializationContext $context = null,
    ): array {
        if ($context instanceof SerializationContext) {
            return $context->computedInputFor(
                $this,
                $metadata,
                $property->isComputed ? null : $property->name,
            );
        }

        $values = [];

        foreach ($metadata->computedInputNamesFor($property->name) as $name) {
            $values[$name] = $this->{$name};
        }

        return $values;
    }

    /**
     * @internal
     */
    protected function computePropertyValue(ClassMetadata $metadata, PropertyMetadata $property, ?SerializationContext $context = null): mixed
    {
        if ($property->computer !== null) {
            $computer = $context?->resolveSerializable($property->computer) ?? new $property->computer();

            if ($computer instanceof ComputesValueInterface) {
                return $computer->compute($this->computedInputFromObject($metadata, $property, $context));
            }
        }

        return $property->hasDefaultValue ? $property->defaultValue : null;
    }

    private static function normalizePlainArrayValue(mixed $value, mixed &$normalized): bool
    {
        if ($value === null || is_string($value) || is_int($value) || is_float($value) || is_bool($value)) {
            $normalized = $value;

            return true;
        }

        if (is_array($value)) {
            $normalized = [];

            foreach ($value as $key => $item) {
                if (!self::normalizePlainArrayValue($item, $normalizedItem)) {
                    $normalized = null;

                    return false;
                }

                $normalized[$key] = $normalizedItem;
            }

            return true;
        }

        if (!$value instanceof stdClass) {
            $normalized = null;

            return false;
        }

        $normalized = [];

        foreach ((array) $value as $key => $item) {
            if (!self::normalizePlainArrayValue($item, $normalizedItem)) {
                $normalized = null;

                return false;
            }

            $normalized[$key] = $normalizedItem;
        }

        return true;
    }
}
