<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Support;

use ArrayAccess;
use Cline\Struct\AbstractData;
use Cline\Struct\Exceptions\ImmutableDataCollectionException;
use Cline\Struct\Serialization\SerializationContext;
use Cline\Struct\Serialization\SerializationOptions;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use IteratorAggregate;
use JsonSerializable;
use stdClass;
use Traversable;

use function array_key_exists;
use function count;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function iterator_to_array;
use function resolve;

/**
 * @author Brian Faust <brian@cline.sh>
 * @template TKey of array-key
 * @template TValue
 * @implements ArrayAccess<TKey, TValue>
 * @implements Arrayable<TKey, TValue>
 * @implements IteratorAggregate<TKey, TValue>
 * @psalm-immutable
 */
final readonly class DataCollection implements Arrayable, ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /** @var array<TKey, TValue> */
    private array $items;

    /**
     * @param iterable<TKey, TValue> $items
     */
    public function __construct(iterable $items = [])
    {
        $this->items = is_array($items)
            ? $items
            : iterator_to_array($items);
    }

    /**
     * @return array<TKey, TValue>
     */
    public function all(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return null|TValue
     */
    public function first(): mixed
    {
        foreach ($this->items as $item) {
            return $item;
        }

        return null;
    }

    /**
     * @return Collection<TKey, TValue>
     */
    public function toCollection(): Collection
    {
        return new Collection($this->items);
    }

    /**
     * @return Traversable<TKey, TValue>
     */
    public function getIterator(): Traversable
    {
        yield from $this->items;
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->items);
    }

    /**
     * @return null|TValue
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    /**
     * @param null|TValue $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw ImmutableDataCollectionException::mutationAttempted();
    }

    public function offsetUnset(mixed $offset): void
    {
        throw ImmutableDataCollectionException::mutationAttempted();
    }

    /**
     * @return array<TKey, mixed>
     */
    public function toArray(): array
    {
        if ($this->items === []) {
            return [];
        }

        $fastPath = $this->serializeFastPath();

        if (is_array($fastPath)) {
            return $fastPath;
        }

        return $this->toArrayUsingContext(
            new SerializationContext(
                new RecursionGuard(),
                resolve(SerializationOptions::class),
            ),
        );
    }

    /**
     * @internal
     * @return array<TKey, mixed>
     */
    public function toArrayUsingContext(SerializationContext $context): array
    {
        $fastPath = $this->serializeFastPath($context);

        if (is_array($fastPath)) {
            return $fastPath;
        }

        $items = [];

        foreach ($this->items as $key => $value) {
            $items[$key] = AbstractData::serializeValueUsingContext($value, $context);
        }

        return $items;
    }

    /**
     * @return array<TKey, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return null|array<TKey, mixed>
     */
    private function serializeFastPath(?SerializationContext $context = null): ?array
    {
        $plainItems = [];
        $onlyPlainValues = true;
        $onlyDataObjects = $context instanceof SerializationContext;

        foreach ($this->items as $key => $item) {
            if ($onlyPlainValues) {
                if ($this->normalizePlainValue($item, $normalized)) {
                    $plainItems[$key] = $normalized;
                } else {
                    $onlyPlainValues = false;
                    $plainItems = [];
                }
            }

            if ($onlyDataObjects && !$item instanceof AbstractData) {
                $onlyDataObjects = false;
            }

            if (!$onlyPlainValues && !$onlyDataObjects) {
                return null;
            }
        }

        if ($onlyPlainValues) {
            return $plainItems;
        }

        if (!$context instanceof SerializationContext || !$onlyDataObjects) {
            return null;
        }

        $serialized = [];

        foreach ($this->items as $key => $item) {
            /** @var AbstractData $item */
            $serialized[$key] = $item->toArrayUsingContext($context);
        }

        return $serialized;
    }

    private function normalizePlainValue(mixed $value, mixed &$normalized): bool
    {
        if (
            $value === null
            || is_string($value)
            || is_int($value)
            || is_float($value)
            || is_bool($value)
        ) {
            $normalized = $value;

            return true;
        }

        if (is_array($value)) {
            $normalized = [];

            foreach ($value as $key => $item) {
                if (!$this->normalizePlainValue($item, $normalizedItem)) {
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
            if (!$this->normalizePlainValue($item, $normalizedItem)) {
                $normalized = null;

                return false;
            }

            $normalized[$key] = $normalizedItem;
        }

        return true;
    }
}
