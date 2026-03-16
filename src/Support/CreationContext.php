<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Support;

use Cline\Struct\Contracts\ComputesValueInterface;
use Cline\Struct\Contracts\ModelPayloadResolverInterface;
use Cline\Struct\Contracts\RequestPayloadResolverInterface;
use Cline\Struct\Metadata\ClassMetadata;
use Cline\Struct\Metadata\CollectionItemRuntime;
use Cline\Struct\Metadata\MetadataFactory;
use Cline\Struct\Metadata\PropertyMetadata;
use Cline\Struct\Resolvers\DefaultModelPayloadResolver;
use Cline\Struct\Resolvers\DefaultRequestPayloadResolver;
use Cline\Struct\Validation\ValidationFactory;
use Closure;
use Illuminate\Support\Collection;
use ReflectionClass;
use Throwable;
use WeakMap;

use function app;
use function array_key_exists;
use function class_exists;
use function function_exists;
use function is_object;
use function is_subclass_of;
use function resolve;

/**
 * Holds per-create dependencies for one hydration operation.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @internal
 */
final class CreationContext
{
    /** @var array<class-string, self> */
    private array $children = [];

    public function __construct(
        public readonly ClassMetadata $metadata,
        private readonly CreationContextCache $cache = new CreationContextCache(),
    ) {}

    public function validationFactory(): ValidationFactory
    {
        if ($this->cache->validationFactory instanceof ValidationFactory) {
            return $this->cache->validationFactory;
        }

        /** @var ValidationFactory $factory */
        $factory = $this->resolveHelper(
            ValidationFactory::class,
            ValidationFactory::class,
            ValidationFactory::class,
        );

        return $this->cache->validationFactory = $factory;
    }

    public function requestPayloadResolver(): RequestPayloadResolverInterface
    {
        if ($this->cache->requestPayloadResolver instanceof RequestPayloadResolverInterface) {
            return $this->cache->requestPayloadResolver;
        }

        /** @var RequestPayloadResolverInterface $resolver */
        $resolver = $this->resolveHelper(
            $this->metadata->requestPayloadResolver,
            RequestPayloadResolverInterface::class,
            DefaultRequestPayloadResolver::class,
        );

        return $this->cache->requestPayloadResolver = $resolver;
    }

    public function modelPayloadResolver(): ModelPayloadResolverInterface
    {
        if ($this->cache->modelPayloadResolver instanceof ModelPayloadResolverInterface) {
            return $this->cache->modelPayloadResolver;
        }

        /** @var ModelPayloadResolverInterface $resolver */
        $resolver = $this->resolveHelper(
            $this->metadata->modelPayloadResolver,
            ModelPayloadResolverInterface::class,
            DefaultModelPayloadResolver::class,
        );

        return $this->cache->modelPayloadResolver = $resolver;
    }

    public function computer(?string $class): ?ComputesValueInterface
    {
        if ($class === null) {
            return null;
        }

        if (array_key_exists($class, $this->cache->computers)) {
            return $this->cache->computers[$class] ?: null;
        }

        if (!class_exists($class) || !is_subclass_of($class, ComputesValueInterface::class)) {
            $this->cache->computerStrategies[$class] = 'invalid';
            $this->cache->computers[$class] = false;

            return null;
        }

        $strategy = $this->cache->computerStrategies[$class] ?? $this->computerStrategyFor($class);

        if ($strategy === 'direct') {
            $computer = new $class();
        } elseif ($strategy === 'container') {
            $computer = $this->resolveComputerFromContainer($class);
        } else {
            $this->cache->computers[$class] = false;

            return null;
        }

        return $this->cache->computers[$class] = $computer;
    }

    public function collectionCallback(?string $class): ?object
    {
        if ($class === null) {
            return null;
        }

        if (array_key_exists($class, $this->cache->collectionCallbacks)) {
            return $this->cache->collectionCallbacks[$class] ?: null;
        }

        if (!class_exists($class)) {
            $this->cache->collectionCallbackStrategies[$class] = 'invalid';
            $this->cache->collectionCallbacks[$class] = false;

            return null;
        }

        $strategy = $this->cache->collectionCallbackStrategies[$class]
            ?? $this->collectionCallbackStrategyFor($class);

        if ($strategy === 'direct') {
            $callback = new $class();
        } elseif ($strategy === 'container') {
            $callback = $this->resolveCollectionCallbackFromContainer($class);
        } else {
            $this->cache->collectionCallbacks[$class] = false;

            return null;
        }

        return $this->cache->collectionCallbacks[$class] = $callback;
    }

    public function collectionItem(PropertyMetadata $property): CollectionItemRuntime
    {
        if (!isset($this->cache->collectionItemProperties[$property])) {
            $this->cache->collectionItemProperties[$property] = new CollectionItemRuntime(
                $property,
                $property->collectionItemDescriptor(),
            );
        }

        return $this->cache->collectionItemProperties[$property];
    }

    /**
     * @return list<object>
     */
    public function propertyAttributes(PropertyMetadata $property): array
    {
        if (isset($this->cache->propertyAttributes[$property])) {
            return $this->cache->propertyAttributes[$property];
        }

        $attributes = [];

        foreach (($property->property?->getAttributes() ?? []) as $attribute) {
            $attributes[] = $attribute->newInstance();
        }

        if ($attributes === []) {
            foreach ($property->parameter->getAttributes() as $attribute) {
                $attributes[] = $attribute->newInstance();
            }
        }

        return $this->cache->propertyAttributes[$property] = $attributes;
    }

    /**
     * @param array<string, mixed> $rawInput
     */
    public function beginHydration(array $rawInput): void
    {
        $this->cache->rawInput = $rawInput;
        $this->cache->propertyHydrationContexts = new WeakMap();
        $this->cache->properties = [];
    }

    public function propertyHydrationContext(PropertyMetadata $property): PropertyHydrationContext
    {
        if (isset($this->cache->propertyHydrationContexts[$property])) {
            return $this->cache->propertyHydrationContexts[$property];
        }

        return $this->cache->propertyHydrationContexts[$property] = new PropertyHydrationContext(
            dataClass: $this->metadata->class,
            property: $property,
            rawInput: $this->cache->rawInput,
            resolvedProperties: $this->cache->properties,
        );
    }

    /**
     * @param array<string, mixed> $properties
     */
    public function setProperties(array $properties): void
    {
        $this->cache->properties = $properties;

        if (!$this->metadata->usesContextualHydration) {
            return;
        }

        $this->cache->propertyHydrationContexts = new WeakMap();
    }

    /**
     * @return array<string, mixed>
     */
    public function properties(): array
    {
        return $this->cache->properties;
    }

    /**
     * @param  Closure(): Collection<array-key, mixed> $resolver
     * @return Collection<array-key, mixed>
     */
    public function materializedCollectionSource(string $property, Closure $resolver): Collection
    {
        if (isset($this->cache->materializedCollectionSources[$property])) {
            return $this->cache->materializedCollectionSources[$property];
        }

        return $this->cache->materializedCollectionSources[$property] = $resolver();
    }

    public function hydrationGuard(): HydrationGuard
    {
        if ($this->cache->hydrationGuard instanceof HydrationGuard) {
            return $this->cache->hydrationGuard;
        }

        return $this->cache->hydrationGuard = new HydrationGuard();
    }

    /**
     * @param class-string $class
     */
    public function child(string $class): self
    {
        return $this->children[$class] ?? $this->children[$class] = new self(
            $this->metadataFactory()->for($class),
            $this->cache,
        );
    }

    private function metadataFactory(): MetadataFactory
    {
        if ($this->cache->metadataFactory instanceof MetadataFactory) {
            return $this->cache->metadataFactory;
        }

        try {
            $factory = resolve(MetadataFactory::class);
        } catch (Throwable) {
            $factory = new MetadataFactory();
        }

        return $this->cache->metadataFactory = $factory;
    }

    /**
     * @param class-string<ComputesValueInterface> $class
     */
    private function computerStrategyFor(string $class): string
    {
        if ($this->canInstantiateDirectly($class)) {
            return $this->cache->computerStrategies[$class] = 'direct';
        }

        if ($this->isBound($class)) {
            return $this->cache->computerStrategies[$class] = 'container';
        }

        return $this->cache->computerStrategies[$class] = 'invalid';
    }

    private function collectionCallbackStrategyFor(string $class): string
    {
        if ($this->canInstantiateDirectly($class)) {
            return $this->cache->collectionCallbackStrategies[$class] = 'direct';
        }

        if ($this->isBound($class)) {
            return $this->cache->collectionCallbackStrategies[$class] = 'container';
        }

        return $this->cache->collectionCallbackStrategies[$class] = 'invalid';
    }

    private function resolveHelper(?string $preferredClass, string $abstract, string $fallbackClass): object
    {
        $strategyKey = $this->helperStrategyKey($preferredClass, $abstract, $fallbackClass);
        $strategy = $this->cache->helperStrategies[$strategyKey]
            ?? $this->helperStrategyFor($strategyKey, $preferredClass, $abstract, $fallbackClass);

        return match ($strategy) {
            'direct' => new ($preferredClass ?? $fallbackClass)(),
            'abstract' => $this->resolveHelperFromContainer($abstract, $abstract, $fallbackClass),
            'preferred' => $this->resolvePreferredHelper($preferredClass, $abstract, $fallbackClass),
            'fallback' => $this->resolveHelperFromContainer($fallbackClass, $abstract, $fallbackClass),
            default => new $fallbackClass(),
        };
    }

    private function resolvePreferredHelper(?string $preferredClass, string $abstract, string $fallbackClass): object
    {
        if ($preferredClass === null) {
            return new $fallbackClass();
        }

        return $this->resolveHelperFromContainer($preferredClass, $abstract, $fallbackClass);
    }

    private function helperStrategyKey(?string $preferredClass, string $abstract, string $fallbackClass): string
    {
        return ($preferredClass ?? 'null').'|'.$abstract.'|'.$fallbackClass;
    }

    private function helperStrategyFor(
        string $key,
        ?string $preferredClass,
        string $abstract,
        string $fallbackClass,
    ): string {
        if ($preferredClass !== null) {
            if ($this->canInstantiateDirectly($preferredClass)) {
                return $this->cache->helperStrategies[$key] = 'direct';
            }

            return $this->cache->helperStrategies[$key] = 'preferred';
        }

        if ($this->isBound($abstract)) {
            return $this->cache->helperStrategies[$key] = 'abstract';
        }

        if ($this->canInstantiateDirectly($fallbackClass)) {
            return $this->cache->helperStrategies[$key] = 'direct';
        }

        return $this->cache->helperStrategies[$key] = 'fallback';
    }

    private function resolveHelperFromContainer(string $class, string $abstract, string $fallbackClass): object
    {
        try {
            $resolved = resolve($class);
        } catch (Throwable) {
            return new $fallbackClass();
        }

        return $resolved instanceof $abstract ? $resolved : new $fallbackClass();
    }

    private function canInstantiateDirectly(string $class): bool
    {
        if (array_key_exists($class, $this->cache->directInstantiationStrategies)) {
            return $this->cache->directInstantiationStrategies[$class];
        }

        if ($this->isBound($class)) {
            return $this->cache->directInstantiationStrategies[$class] = false;
        }

        if (!class_exists($class)) {
            return $this->cache->directInstantiationStrategies[$class] = false;
        }

        $reflection = new ReflectionClass($class);

        $constructor = $reflection->getConstructor();

        return $this->cache->directInstantiationStrategies[$class] = $constructor === null
            || $constructor->getNumberOfRequiredParameters() === 0;
    }

    /**
     * @param class-string<ComputesValueInterface> $class
     */
    private function resolveComputerFromContainer(string $class): ComputesValueInterface
    {
        return resolve($class);
    }

    private function resolveCollectionCallbackFromContainer(string $class): object
    {
        $resolved = resolve($class);

        if (is_object($resolved)) {
            return $resolved;
        }

        return new $class();
    }

    private function isBound(string $class): bool
    {
        return function_exists('app') && app()->bound($class);
    }
}

/**
 * @author Brian Faust <brian@cline.sh>
 * @internal
 */
final class CreationContextCache
{
    /** @var WeakMap<PropertyMetadata, CollectionItemRuntime> */
    public WeakMap $collectionItemProperties;

    /** @var WeakMap<PropertyMetadata, list<object>> */
    public WeakMap $propertyAttributes;

    /** @var WeakMap<PropertyMetadata, PropertyHydrationContext> */
    public WeakMap $propertyHydrationContexts;

    public ?HydrationGuard $hydrationGuard = null;

    public ?ValidationFactory $validationFactory = null;

    public ?RequestPayloadResolverInterface $requestPayloadResolver = null;

    public ?ModelPayloadResolverInterface $modelPayloadResolver = null;

    public ?MetadataFactory $metadataFactory = null;

    /** @var array<string, ComputesValueInterface|false> */
    public array $computers = [];

    /** @var array<string, 'container'|'direct'|'invalid'> */
    public array $computerStrategies = [];

    /** @var array<string, false|object> */
    public array $collectionCallbacks = [];

    /** @var array<string, 'container'|'direct'|'invalid'> */
    public array $collectionCallbackStrategies = [];

    /** @var array<string, 'abstract'|'direct'|'fallback'|'preferred'> */
    public array $helperStrategies = [];

    /** @var array<string, bool> */
    public array $directInstantiationStrategies = [];

    /** @var array<string, mixed> */
    public array $properties = [];

    /** @var array<string, mixed> */
    public array $rawInput = [];

    /** @var array<string, Collection<array-key, mixed>> */
    public array $materializedCollectionSources = [];

    public function __construct()
    {
        $this->collectionItemProperties = new WeakMap();
        $this->propertyAttributes = new WeakMap();
        $this->propertyHydrationContexts = new WeakMap();
    }
}
