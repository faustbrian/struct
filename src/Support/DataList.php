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
use Cline\Struct\Exceptions\ImmutableDataListException;
use Cline\Struct\Exceptions\MissingDataListIndexException;
use Cline\Struct\Serialization\SerializationContext;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use IteratorAggregate;
use JsonSerializable;
use OutOfBoundsException;
use stdClass;
use Traversable;

use function array_key_exists;
use function array_keys;
use function array_map;
use function array_values;
use function count;
use function get_object_vars;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function iterator_to_array;
use function throw_unless;

/**
 * @author Brian Faust <brian@cline.sh>
 * @template TValue
 * @implements ArrayAccess<int, TValue>
 * @implements Arrayable<int, TValue>
 * @implements IteratorAggregate<int, TValue>
 * @psalm-immutable
 */
final readonly class DataList implements Arrayable, ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /** @var list<TValue> */
    private array $items;

    /**
     * @param iterable<TValue> $items
     */
    public function __construct(iterable $items = [])
    {
        $this->items = array_values(
            is_array($items) ? $items : iterator_to_array($items, false),
        );
    }

    /**
     * @return list<TValue>
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
        return $this->items[0] ?? null;
    }

    /**
     * @throws OutOfBoundsException
     * @return TValue
     */
    public function get(int $index): mixed
    {
        throw_unless(array_key_exists($index, $this->items), MissingDataListIndexException::forIndex($index));

        return $this->items[$index];
    }

    /**
     * @return Traversable<int, TValue>
     */
    public function getIterator(): Traversable
    {
        yield from $this->items;
    }

    /**
     * @param  callable(TValue, int): TValue $callback
     * @return self<TValue>
     */
    public function map(callable $callback): self
    {
        return new self(
            array_map(
                $callback,
                $this->items,
                array_keys($this->items),
            ),
        );
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
     * @param  null|TValue          $value
     * @throws OutOfBoundsException
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw ImmutableDataListException::mutationAttempted();
    }

    /**
     * @throws OutOfBoundsException
     */
    public function offsetUnset(mixed $offset): void
    {
        throw ImmutableDataListException::mutationAttempted();
    }

    /**
     * @return list<TValue>
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * @internal
     * @return list<mixed>
     */
    public function toArrayUsingContext(SerializationContext $context): array
    {
        $fastPath = $this->serializeFastPath($context);

        if (is_array($fastPath)) {
            return $fastPath;
        }

        $items = [];

        foreach ($this->items as $item) {
            $items[] = AbstractData::serializeValueUsingContext($item, $context);
        }

        return $items;
    }

    /**
     * @return list<TValue>
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }

    /**
     * @return null|list<mixed>
     */
    private function serializeFastPath(?SerializationContext $context = null): ?array
    {
        $plainItems = [];
        $serializedDataObjects = [];
        $onlyPlainValues = true;
        $onlyDataObjects = $context instanceof SerializationContext;

        foreach ($this->items as $item) {
            if ($onlyPlainValues) {
                if ($this->normalizePlainValue($item, $normalized)) {
                    $plainItems[] = $normalized;
                } else {
                    $onlyPlainValues = false;
                    $plainItems = [];
                }
            }

            if ($onlyDataObjects) {
                if ($context instanceof SerializationContext && $item instanceof AbstractData) {
                    $serializedDataObjects[] = $item->toArrayUsingContext($context);
                } else {
                    $onlyDataObjects = false;
                    $serializedDataObjects = [];
                }
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

        return $serializedDataObjects;
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

            foreach ($value as $item) {
                if (!$this->normalizePlainValue($item, $normalizedItem)) {
                    $normalized = null;

                    return false;
                }

                $normalized[] = $normalizedItem;
            }

            return true;
        }

        if (!$value instanceof stdClass) {
            $normalized = null;

            return false;
        }

        $normalized = [];

        foreach (get_object_vars($value) as $key => $item) {
            if (!$this->normalizePlainValue($item, $normalizedItem)) {
                $normalized = null;

                return false;
            }

            $normalized[$key] = $normalizedItem;
        }

        return true;
    }
}
