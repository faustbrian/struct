<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\Collections;

use Cline\Struct\Contracts\TransformsLaravelCollectionValueInterface;
use Cline\Struct\Exceptions\InvalidCollectionAttributeException;
use Cline\Struct\Support\CreationContext;
use Illuminate\Support\Collection;
use Throwable;

use function array_key_exists;
use function class_exists;
use function is_array;
use function is_object;
use function resolve;

/**
 * Base attribute for Laravel collection transforms that need operand items.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
abstract readonly class AbstractCollectionOperandTransformer implements TransformsLaravelCollectionValueInterface
{
    /**
     * @param null|array<array-key, mixed> $values
     */
    public function __construct(
        public ?string $source = null,
        public ?array $values = null,
    ) {}

    /**
     * @return array<array-key, mixed>|Collection<array-key, mixed>
     */
    protected function resolveOperand(?CreationContext $context = null): array|Collection
    {
        $hasSource = $this->source !== null;
        $hasValues = $this->values !== null;

        if ($hasSource === $hasValues) {
            throw InvalidCollectionAttributeException::forInvalidOperandConfiguration(static::class);
        }

        if ($this->values !== null) {
            return $this->values;
        }

        $properties = $context?->properties() ?? [];

        if (!array_key_exists($this->source, $properties)) {
            throw InvalidCollectionAttributeException::forInvalidOperandSource(static::class, (string) $this->source);
        }

        $operand = $properties[$this->source];

        if ($operand instanceof Collection || is_array($operand)) {
            return $operand;
        }

        throw InvalidCollectionAttributeException::forInvalidOperandSource(static::class, (string) $this->source);
    }

    protected function resolveCallback(string $class, string $expected, ?CreationContext $context = null): object
    {
        $resolved = $context?->collectionCallback($class);

        if (!is_object($resolved) && class_exists($class)) {
            try {
                $resolved = resolve($class);
            } catch (Throwable) {
                $resolved = new $class();
            }
        }

        if ($resolved instanceof $expected) {
            return $resolved;
        }

        throw InvalidCollectionAttributeException::forInvalidCallback(
            static::class,
            $class,
            $expected,
        );
    }
}
