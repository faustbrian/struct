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
use Cline\Struct\Serialization\SerializationOptions;
use Closure;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use IteratorAggregate;
use JsonSerializable;
use OutOfBoundsException;
use Traversable;

use function max;
use function resolve;
use function throw_unless;

/**
 * @author Brian Faust <brian@cline.sh>
 * @template TValue
 * @implements ArrayAccess<int, TValue>
 * @implements Arrayable<int, TValue>
 * @implements IteratorAggregate<int, TValue>
 * @psalm-immutable
 */
final readonly class LazyDataList implements Arrayable, ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /** @var LazyCollectionState<int, TValue> */
    private LazyCollectionState $state;

    /**
     * @param iterable<array-key, mixed>       $items
     * @param null|Closure(int, mixed): TValue $hydrate
     */
    public function __construct(
        iterable $items = [],
        ?Closure $hydrate = null,
    ) {
        /** @var LazyCollectionState<int, TValue> $state */
        $state = new LazyCollectionState(
            $items,
            $hydrate ?? static fn (int $key, mixed $value): mixed => $value,
            true,
        );

        $this->state = $state;
    }

    /**
     * @return list<TValue>
     */
    public function all(): array
    {
        /** @var list<TValue> */
        return $this->state->all();
    }

    public function count(): int
    {
        return max(0, $this->state->count());
    }

    /**
     * @return null|TValue
     */
    public function first(): mixed
    {
        return $this->state->first();
    }

    /**
     * @throws OutOfBoundsException
     * @return TValue
     */
    public function get(int $index): mixed
    {
        throw_unless($this->state->has($index), MissingDataListIndexException::forIndex($index));

        return $this->state->resolved($index);
    }

    /**
     * @return Traversable<int, TValue>
     */
    public function getIterator(): Traversable
    {
        yield from $this->state->iterate();
    }

    /**
     * @param  callable(TValue, int): TValue $callback
     * @return self<TValue>
     */
    public function map(callable $callback): self
    {
        return new self(
            (function () use ($callback): Traversable {
                foreach ($this as $index => $item) {
                    yield $index => $callback($item, $index);
                }
            })(),
        );
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->state->has($offset);
    }

    /**
     * @return null|TValue
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->state->get($offset);
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
     * @return list<mixed>
     */
    public function toArray(): array
    {
        return $this->toArrayUsingContext(
            new SerializationContext(
                new RecursionGuard(),
                resolve(SerializationOptions::class),
            ),
        );
    }

    /**
     * @internal
     * @return list<mixed>
     */
    public function toArrayUsingContext(SerializationContext $context): array
    {
        $items = [];

        foreach ($this as $item) {
            $items[] = AbstractData::serializeValueUsingContext($item, $context);
        }

        return $items;
    }

    /**
     * @return list<mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
