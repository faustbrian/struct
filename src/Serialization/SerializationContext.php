<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Serialization;

use Cline\Struct\Metadata\ClassMetadata;
use Cline\Struct\Metadata\CollectionItemRuntime;
use Cline\Struct\Metadata\MetadataFactory;
use Cline\Struct\Metadata\PropertyMetadata;
use Cline\Struct\Support\RecursionGuard;
use WeakMap;

use function array_key_exists;
use function resolve;

/**
 * Shares traversal state across one serialization pass.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @internal
 */
final class SerializationContext
{
    /** @var WeakMap<object, array<string, mixed>> */
    private WeakMap $hydratedInputs;

    /** @var WeakMap<object, array<string, array<string, mixed>>> */
    private WeakMap $computedInputsByExcludedProperty;

    /** @var array<class-string, ClassMetadata> */
    private array $metadata = [];

    /** @var array<class-string, ProjectionPlan> */
    private array $projectionPlans = [];

    /** @var array<string, self> */
    private array $children = [];

    /** @var WeakMap<PropertyMetadata, CollectionItemRuntime> */
    private WeakMap $collectionItemProperties;

    public function __construct(
        public readonly RecursionGuard $guard,
        public readonly SerializationOptions $options,
        private readonly SerializableResolverCache $resolverCache = new SerializableResolverCache(),
        private ?MetadataFactory $metadataFactory = null,
    ) {
        $this->hydratedInputs = new WeakMap();
        $this->computedInputsByExcludedProperty = new WeakMap();
        $this->collectionItemProperties = new WeakMap();
    }

    public function child(string $path): self
    {
        if (isset($this->children[$path])) {
            return $this->children[$path];
        }

        $options = $this->options->child($path);

        if ($options === $this->options) {
            return $this;
        }

        $child = new self(
            $this->guard,
            $options,
            $this->resolverCache,
            $this->metadataFactory,
        );
        $child->metadata = $this->metadata;
        $child->collectionItemProperties = $this->collectionItemProperties;

        return $this->children[$path] = $child;
    }

    public function collectionItem(PropertyMetadata $property): CollectionItemRuntime
    {
        if (!isset($this->collectionItemProperties[$property])) {
            $this->collectionItemProperties[$property] = new CollectionItemRuntime(
                $property,
                $property->collectionItemDescriptor(),
            );
        }

        return $this->collectionItemProperties[$property];
    }

    public function resolveSerializable(?string $class): ?object
    {
        return $this->resolverCache->resolve($class);
    }

    public function usesDefaultProjection(): bool
    {
        return $this->options->usesDefaultProjection();
    }

    public function projectionPlanFor(ClassMetadata $metadata): ProjectionPlan
    {
        if (isset($this->projectionPlans[$metadata->class])) {
            return $this->projectionPlans[$metadata->class];
        }

        $entries = [];

        foreach ($metadata->properties as $property) {
            if (!$this->options->includeSensitive && $property->isSensitive) {
                continue;
            }

            if ($this->options->shouldExcludePath($property->outputName)) {
                continue;
            }

            if ($property->includeConditions !== [] || $property->excludeConditions !== []) {
                $entries[] = new ProjectionEntry(
                    property: $property,
                    conditional: true,
                    derived: $property->lazyResolver !== null || ($property->isComputed && $property->isLazy),
                );

                continue;
            }

            if (!$property->isLazy) {
                $entries[] = new ProjectionEntry(
                    property: $property,
                    conditional: false,
                    derived: false,
                );

                continue;
            }

            if (
                !$this->options->shouldIncludePath($property->outputName)
                && !$this->options->includesGroup($property->lazyGroups)
            ) {
                continue;
            }

            $entries[] = new ProjectionEntry(
                property: $property,
                conditional: false,
                derived: $property->lazyResolver !== null || $property->isComputed,
            );
        }

        return $this->projectionPlans[$metadata->class] = new ProjectionPlan(
            entries: $entries,
        );
    }

    /**
     * @param class-string              $class
     * @param callable(): ClassMetadata $resolver
     */
    public function metadataFor(string $class, callable $resolver): ClassMetadata
    {
        return $this->metadata[$class] ??= $resolver();
    }

    /**
     * @param class-string $class
     */
    public function cachedMetadata(string $class): ?ClassMetadata
    {
        return $this->metadata[$class] ?? null;
    }

    public function rememberMetadata(ClassMetadata $metadata): ClassMetadata
    {
        return $this->metadata[$metadata->class] ??= $metadata;
    }

    /**
     * @param class-string $class
     */
    public function metadataForClass(string $class): ClassMetadata
    {
        return $this->metadata[$class] ??= $this->metadataFactory()->for($class);
    }

    /**
     * @return array<string, mixed>
     */
    public function computedInputFor(
        object $data,
        ClassMetadata $metadata,
        ?string $excludedProperty = null,
    ): array {
        if (!isset($this->hydratedInputs[$data])) {
            $this->hydratedInputs[$data] = $this->hydratedInputFor($data, $metadata);
        }

        if ($excludedProperty === null) {
            return $this->hydratedInputs[$data];
        }

        if (!isset($this->computedInputsByExcludedProperty[$data])) {
            $this->computedInputsByExcludedProperty[$data] = [];
        }

        if (isset($this->computedInputsByExcludedProperty[$data][$excludedProperty])) {
            return $this->computedInputsByExcludedProperty[$data][$excludedProperty];
        }

        $names = $metadata->computedInputNamesFor($excludedProperty);

        if ($names === []) {
            return $this->computedInputsByExcludedProperty[$data][$excludedProperty] = [];
        }

        $hydratedInputs = $this->hydratedInputs[$data];
        $inputs = [];

        foreach ($names as $name) {
            if (!isset($hydratedInputs[$name]) && !array_key_exists($name, $hydratedInputs)) {
                $inputs[$name] = $data->{$name};

                continue;
            }

            $inputs[$name] = $hydratedInputs[$name];
        }

        return $this->computedInputsByExcludedProperty[$data][$excludedProperty] = $inputs;
    }

    /**
     * @return array<string, mixed>
     */
    private function hydratedInputFor(object $data, ClassMetadata $metadata): array
    {
        $inputs = [];

        foreach ($metadata->hydratedPropertyNames as $name) {
            $inputs[$name] = $data->{$name};
        }

        return $inputs;
    }

    private function metadataFactory(): MetadataFactory
    {
        return $this->metadataFactory ??= resolve(MetadataFactory::class);
    }
}
