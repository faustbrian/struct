<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Support;

use Closure;
use Generator;
use Iterator;
use IteratorAggregate;
use IteratorIterator;

use function array_key_exists;
use function array_key_last;
use function count;
use function is_array;
use function is_int;
use function is_string;

/**
 * @internal
 * @template TKey of array-key
 * @template TValue
 */
final class LazyCollectionState
{
    /** @var array<TKey, TValue> */
    private array $items = [];

    /** @var null|Iterator<array-key, mixed> */
    private ?Iterator $iterator = null;

    private bool $started = false;

    private bool $exhausted = false;

    private int $nextListIndex = 0;

    /**
     * @param iterable<array-key, mixed>   $source
     * @param Closure(TKey, mixed): TValue $hydrate
     */
    public function __construct(
        private readonly iterable $source,
        private readonly Closure $hydrate,
        private readonly bool $normalizeKeys,
    ) {}

    /**
     * @return array<TKey, TValue>
     */
    public function all(): array
    {
        $this->consumeAll();

        return $this->items;
    }

    public function count(): int
    {
        $this->consumeAll();

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

        if (!$this->consumeNext()) {
            return null;
        }

        foreach ($this->items as $item) {
            return $item;
        }

        return null;
    }

    /**
     * @return Generator<TKey, TValue>
     */
    public function iterate(): Generator
    {
        foreach ($this->items as $key => $item) {
            yield $key => $item;
        }

        while ($this->consumeNext()) {
            $key = array_key_last($this->items);

            if ($key === null) {
                continue;
            }

            /** @var TKey $key */
            yield $key => $this->items[$key];
        }
    }

    public function has(mixed $key): bool
    {
        if (!$this->isValidRequestedKey($key)) {
            return false;
        }

        /** @var int|string $key */
        $this->consumeUntilKey($key);

        return array_key_exists($key, $this->items);
    }

    /**
     * @return null|TValue
     */
    public function get(mixed $key): mixed
    {
        if (!$this->isValidRequestedKey($key)) {
            return null;
        }

        /** @var int|string $key */
        $this->consumeUntilKey($key);

        return $this->items[$key] ?? null;
    }

    /**
     * @return TValue
     */
    public function resolved(int|string $key): mixed
    {
        return $this->items[$key];
    }

    private function consumeAll(): void
    {
        while ($this->consumeNext()) {
            // Continue until the source is exhausted.
        }
    }

    private function consumeUntilKey(int|string $key): void
    {
        while (!array_key_exists($key, $this->items) && $this->consumeNext()) {
            // Continue until the requested key is hydrated or the source ends.
        }
    }

    private function consumeNext(): bool
    {
        if ($this->exhausted) {
            return false;
        }

        $iterator = $this->iterator();

        if (!$this->started) {
            $iterator->rewind();
            $this->started = true;
        } else {
            $iterator->next();
        }

        if (!$iterator->valid()) {
            $this->exhausted = true;

            return false;
        }

        $key = $this->normalizeKeys ? $this->nextListKey() : $iterator->key();

        /** @var TKey $key */
        $this->items[$key] = ($this->hydrate)($key, $iterator->current());

        return true;
    }

    /**
     * @return Iterator<array-key, mixed>
     */
    private function iterator(): Iterator
    {
        if ($this->iterator instanceof Iterator) {
            return $this->iterator;
        }

        if (is_array($this->source)) {
            return $this->iterator = new IteratorIterator((function (array $items): Generator {
                yield from $items;
            })($this->source));
        }

        if ($this->source instanceof Iterator) {
            return $this->iterator = $this->source;
        }

        if ($this->source instanceof IteratorAggregate) {
            $iterator = $this->source->getIterator();

            return $this->iterator = $iterator instanceof Iterator
                ? $iterator
                : new IteratorIterator($iterator);
        }

        return $this->iterator = new IteratorIterator($this->source);
    }

    private function nextListKey(): int
    {
        return $this->nextListIndex++;
    }

    private function isValidRequestedKey(mixed $key): bool
    {
        if ($this->normalizeKeys) {
            return is_int($key);
        }

        return is_int($key) || is_string($key);
    }
}
