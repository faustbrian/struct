<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Metadata;

use ReflectionClass;

use function array_fill_keys;
use function array_keys;
use function is_array;
use function is_string;

/**
 * Stores the reflected metadata needed to hydrate and validate a data object.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class ClassMetadata
{
    /** @var array<string, true> */
    public array $inputNameLookup;

    /** @var array<string, true> */
    public array $hydratedPropertyLookup;

    /** @var list<PropertyMetadata> */
    public array $hydratedProperties;

    /** @var list<string> */
    public array $hydratedPropertyNames;

    /** @var list<PropertyMetadata> */
    public array $computedProperties;

    /** @var list<PropertyMetadata> */
    public array $collectionSourceProperties;

    /** @var list<PropertyMetadata> */
    public array $collectionResultProperties;

    /** @var list<PropertyMetadata> */
    public array $defaultProjectionProperties;

    /** @var list<PropertyMetadata> */
    public array $defaultProjectionPropertiesWithoutSensitive;

    /** @var array<string, list<string>> */
    public array $computedInputNames;

    /**
     * @param class-string                    $class
     * @param ReflectionClass<object>         $reflection
     * @param array<string, PropertyMetadata> $properties
     */
    public function __construct(
        public string $class,
        public ReflectionClass $reflection,
        public array $properties,
        public bool $forbidUndefinedValues,
        public bool $forbidSuperfluousKeys,
        public bool $inferValidationRules,
        public ?string $validatorMutator,
        public ?string $requestPayloadResolver,
        public ?string $modelPayloadResolver,
        public ?string $stringifier,
        public ?string $factory,
    ) {
        $hydratedProperties = [];
        $computedProperties = [];
        $collectionSourceProperties = [];
        $collectionResultProperties = [];
        $inputNames = [];

        $defaultProjectionProperties = [];
        $defaultProjectionPropertiesWithoutSensitive = [];
        $hydratedPropertyNames = [];
        $hydratedPropertyLookup = [];

        foreach ($this->properties as $property) {
            if ($property->hasCollectionSourceAttribute) {
                $collectionSourceProperties[] = $property;
            } elseif ($property->hasCollectionResultAttribute) {
                $collectionResultProperties[] = $property;
            } elseif ($property->isComputed) {
                $computedProperties[] = $property;
            } else {
                $inputNames[] = $property->inputName;
                $hydratedProperties[] = $property;
                $hydratedPropertyNames[] = $property->name;
                $hydratedPropertyLookup[$property->name] = true;
            }

            if ($property->isLazy) {
                continue;
            }

            if ($property->excludeConditions !== []) {
                continue;
            }

            $defaultProjectionProperties[] = $property;

            if ($property->isSensitive) {
                continue;
            }

            $defaultProjectionPropertiesWithoutSensitive[] = $property;
        }

        $this->hydratedProperties = $hydratedProperties;
        $this->hydratedPropertyNames = $hydratedPropertyNames;
        $this->computedProperties = $computedProperties;
        $this->collectionSourceProperties = $collectionSourceProperties;
        $this->collectionResultProperties = $collectionResultProperties;
        $this->inputNameLookup = array_fill_keys($inputNames, true);
        $this->hydratedPropertyLookup = $hydratedPropertyLookup;
        $this->defaultProjectionProperties = $defaultProjectionProperties;
        $this->defaultProjectionPropertiesWithoutSensitive = $defaultProjectionPropertiesWithoutSensitive;
        $this->computedInputNames = $this->buildComputedInputNames();
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromCachePayload(array $payload): self
    {
        /** @var class-string $class */
        $class = $payload['class'];
        $reflection = new ReflectionClass($class);
        $parameters = [];

        foreach ($reflection->getConstructor()?->getParameters() ?? [] as $parameter) {
            $parameters[$parameter->getName()] = $parameter;
        }

        $properties = [];
        $propertiesPayload = $payload['properties'] ?? [];

        if (!is_array($propertiesPayload)) {
            $propertiesPayload = [];
        }

        foreach ($propertiesPayload as $name => $propertyPayload) {
            if (!is_string($name)) {
                continue;
            }

            if (!is_array($propertyPayload)) {
                continue;
            }

            if (!isset($parameters[$name])) {
                continue;
            }

            /** @var array<string, mixed> $propertyPayload */
            $properties[$name] = PropertyMetadata::fromCachePayload(
                $class,
                $reflection,
                $parameters[$name],
                $propertyPayload,
            );
        }

        return new self(
            class: $class,
            reflection: $reflection,
            properties: $properties,
            forbidUndefinedValues: (bool) ($payload['forbidUndefinedValues'] ?? false),
            forbidSuperfluousKeys: (bool) ($payload['forbidSuperfluousKeys'] ?? false),
            inferValidationRules: (bool) ($payload['inferValidationRules'] ?? false),
            validatorMutator: is_string($payload['validatorMutator'] ?? null) ? $payload['validatorMutator'] : null,
            requestPayloadResolver: is_string($payload['requestPayloadResolver'] ?? null) ? $payload['requestPayloadResolver'] : null,
            modelPayloadResolver: is_string($payload['modelPayloadResolver'] ?? null) ? $payload['modelPayloadResolver'] : null,
            stringifier: is_string($payload['stringifier'] ?? null) ? $payload['stringifier'] : null,
            factory: is_string($payload['factory'] ?? null) ? $payload['factory'] : null,
        );
    }

    /**
     * Return the external input names accepted by the data object.
     *
     * @return list<string>
     */
    public function inputNames(): array
    {
        return array_keys($this->inputNameLookup);
    }

    /**
     * @return list<string>
     */
    public function computedInputNamesFor(string $property): array
    {
        if (isset($this->computedInputNames[$property])) {
            return $this->computedInputNames[$property];
        }

        if (!isset($this->properties[$property])) {
            return [];
        }

        $names = [];

        foreach ($this->hydratedProperties as $hydratedProperty) {
            if ($hydratedProperty->name === $property) {
                continue;
            }

            $names[] = $hydratedProperty->name;
        }

        return $names;
    }

    /**
     * @return array<string, mixed>
     */
    public function toCachePayload(): array
    {
        return [
            'class' => $this->class,
            'forbidUndefinedValues' => $this->forbidUndefinedValues,
            'forbidSuperfluousKeys' => $this->forbidSuperfluousKeys,
            'inferValidationRules' => $this->inferValidationRules,
            'validatorMutator' => $this->validatorMutator,
            'requestPayloadResolver' => $this->requestPayloadResolver,
            'modelPayloadResolver' => $this->modelPayloadResolver,
            'stringifier' => $this->stringifier,
            'factory' => $this->factory,
            'properties' => $this->propertiesPayload(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function propertiesPayload(): array
    {
        $payload = [];

        foreach ($this->properties as $property) {
            $payload[$property->name] = $property->toCachePayload();
        }

        return $payload;
    }

    /**
     * @return array<string, list<string>>
     */
    private function buildComputedInputNames(): array
    {
        $computedInputNames = [];

        foreach ($this->properties as $property) {
            if (!$property->isComputed) {
                continue;
            }

            $computedInputNames[$property->name] = $this->hydratedPropertyNames;
        }

        return $computedInputNames;
    }
}
