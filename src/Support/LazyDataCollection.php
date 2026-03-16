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
use Cline\Struct\Serialization\SerializationDefaults;
use Cline\Struct\Serialization\SerializationOptions;
use Closure;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

use function max;
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
final readonly class LazyDataCollection implements Arrayable, ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /** @var LazyCollectionState<TKey, TValue> */
    private LazyCollectionState $state;

    /**
     * @param iterable<TKey, mixed>             $items
     * @param null|Closure(TKey, mixed): TValue $hydrate
     */
    public function __construct(
        iterable $items = [],
        ?Closure $hydrate = null,
    ) {
        /** @var LazyCollectionState<TKey, TValue> $state */
        $state = new LazyCollectionState(
            $items,
            $hydrate ?? static fn (int|string $key, mixed $value): mixed => $value,
            false,
        );

        $this->state = $state;
    }

    /**
     * @return array<TKey, TValue>
     */
    public function all(): array
    {
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
     * @return Collection<TKey, TValue>
     */
    public function toCollection(): Collection
    {
        return new Collection($this->all());
    }

    /**
     * @return Traversable<TKey, TValue>
     */
    public function getIterator(): Traversable
    {
        yield from $this->state->iterate();
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
        $defaults = resolve(SerializationDefaults::class);

        return $this->toArrayUsingContext(
            new SerializationContext(
                new RecursionGuard(),
                $defaults->options,
                metadataFactory: $defaults->metadataFactory,
            ),
        );
    }

    /**
     * @internal
     * @return array<TKey, mixed>
     */
    public function toArrayUsingContext(SerializationContext $context): array
    {
        $items = [];

        foreach ($this as $key => $value) {
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
}
