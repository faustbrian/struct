<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Factories;

use Cline\Struct\Contracts\DataObjectInterface;
use Closure;
use Generator;
use Illuminate\Support\LazyCollection;

use function array_merge;
use function array_values;
use function count;
use function is_callable;

/**
 * @author Brian Faust <brian@cline.sh>
 * @phpstan-consistent-constructor
 */
abstract class AbstractFactory
{
    /** @var class-string<DataObjectInterface> */
    protected string $model;

    protected int $count = 1;

    /** @var array<int, array<string, mixed>|callable(array<string, mixed>, int): array<string, mixed>> */
    protected array $states = [];

    /** @var list<array<string, mixed>> */
    protected array $sequence = [];

    /** @var array<int, Closure(DataObjectInterface): (null|DataObjectInterface)> */
    protected array $afterMaking = [];

    /** @var array<int, Closure(DataObjectInterface): (null|DataObjectInterface)> */
    protected array $afterCreating = [];

    public static function new(): static
    {
        return new static()->configure();
    }

    // ABSTRACT_MARKER
    public function configure(): static
    {
        return $this;
    }

    public function count(int $count): static
    {
        $clone = clone $this;
        $clone->count = $count;

        return $clone;
    }

    public function times(int $count): static
    {
        return $this->count($count);
    }

    /**
     * @param array<string, mixed>|callable(array<string, mixed>, int): array<string, mixed> $state
     */
    public function state(array|callable $state): static
    {
        $clone = clone $this;
        $clone->states[] = $state;

        return $clone;
    }

    /**
     * @param array<string, mixed> ...$sequence
     */
    public function sequence(array ...$sequence): static
    {
        $clone = clone $this;

        /** @var list<array<string, mixed>> $normalized */
        $normalized = array_values($sequence);
        $clone->sequence = $normalized;

        return $clone;
    }

    /**
     * @param Closure(DataObjectInterface): (null|DataObjectInterface) $callback
     */
    public function afterMaking(Closure $callback): static
    {
        $clone = clone $this;
        $clone->afterMaking[] = $callback;

        return $clone;
    }

    /**
     * @param Closure(DataObjectInterface): (null|DataObjectInterface) $callback
     */
    public function afterCreating(Closure $callback): static
    {
        $clone = clone $this;
        $clone->afterCreating[] = $callback;

        return $clone;
    }

    /**
     * @param  array<string, mixed>                          $attributes
     * @return DataObjectInterface|list<DataObjectInterface>
     */
    public function make(array $attributes = []): DataObjectInterface|array
    {
        /** @var list<DataObjectInterface> $items */
        $items = [];

        for ($index = 0; $index < $this->count; ++$index) {
            $items[] = $this->applyCallbacks(
                $this->model::create($this->definitionForIndex($index, $attributes)),
                $this->afterMaking,
            );
        }

        return $this->count === 1 ? $items[0] : $items;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function makeOne(array $attributes = []): DataObjectInterface
    {
        $result = $this->count(1)->make($attributes);

        if ($result instanceof DataObjectInterface) {
            return $result;
        }

        return $result[0];
    }

    /**
     * @param  array<string, mixed>                            $attributes
     * @return array<string, mixed>|list<array<string, mixed>>
     */
    public function raw(array $attributes = []): array
    {
        $items = [];

        for ($index = 0; $index < $this->count; ++$index) {
            $items[] = $this->definitionForIndex($index, $attributes);
        }

        return $this->count === 1 ? $items[0] : $items;
    }

    /**
     * @param  array<string, mixed>                          $attributes
     * @return DataObjectInterface|list<DataObjectInterface>
     */
    public function create(array $attributes = []): DataObjectInterface|array
    {
        $result = $this->make($attributes);

        if ($result instanceof DataObjectInterface) {
            return $this->applyCallbacks($result, $this->afterCreating);
        }

        $created = [];

        foreach ($result as $dto) {
            $created[] = $this->applyCallbacks($dto, $this->afterCreating);
        }

        return $created;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function createOne(array $attributes = []): DataObjectInterface
    {
        $result = $this->create($attributes);

        if ($result instanceof DataObjectInterface) {
            return $result;
        }

        return $result[0];
    }

    /**
     * @param  array<string, mixed>                     $attributes
     * @return LazyCollection<int, DataObjectInterface>
     */
    public function lazy(array $attributes = []): LazyCollection
    {
        return LazyCollection::make(function () use ($attributes): Generator {
            for ($index = 0; $index < $this->count; ++$index) {
                yield $this->applyCallbacks(
                    $this->model::create($this->definitionForIndex($index, $attributes)),
                    $this->afterMaking,
                );
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    abstract public function definition(): array;

    /**
     * @param  array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    private function definitionForIndex(int $index, array $attributes): array
    {
        $definition = $this->definition();

        foreach ($this->states as $state) {
            /** @var array<string, mixed> $stateValues */
            $stateValues = is_callable($state) ? $state($definition, $index) : $state;
            $definition = array_merge(
                $definition,
                $stateValues,
            );
        }

        if ($this->sequence !== []) {
            $definition = array_merge($definition, $this->sequence[$index % count($this->sequence)]);
        }

        return array_merge($definition, $attributes);
    }

    /**
     * @param array<int, Closure(DataObjectInterface): (null|DataObjectInterface)> $callbacks
     */
    private function applyCallbacks(DataObjectInterface $dto, array $callbacks): DataObjectInterface
    {
        foreach ($callbacks as $callback) {
            $result = $callback($dto);

            if (!$result instanceof DataObjectInterface) {
                continue;
            }

            $dto = $result;
        }

        return $dto;
    }
}
