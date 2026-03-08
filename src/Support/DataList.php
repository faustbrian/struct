<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Support;

use ArrayAccess;
use Cline\Struct\Exceptions\ImmutableDataListException;
use Cline\Struct\Exceptions\MissingDataListIndexException;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use IteratorAggregate;
use JsonSerializable;
use OutOfBoundsException;
use Traversable;

use function array_key_exists;
use function array_keys;
use function array_map;
use function array_values;
use function count;
use function is_array;
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
     * @return list<TValue>
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
