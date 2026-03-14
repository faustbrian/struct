<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\Collections;

use Cline\Struct\Contracts\DecidesCollectionPipelineConditionInterface;
use Cline\Struct\Exceptions\InvalidCollectionAttributeException;
use Cline\Struct\Support\CreationContext;
use Illuminate\Support\Collection;
use Throwable;

use function class_exists;
use function is_object;
use function resolve;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
abstract readonly class AbstractConditionalCollectionCallbackTransformer extends AbstractConditionalCollectionTransformer
{
    /**
     * @param class-string $callback
     */
    public function __construct(
        public string $callback,
    ) {}

    /**
     * @param Collection<array-key, mixed> $items
     */
    protected function shouldApply(Collection $items, ?CreationContext $context = null): bool
    {
        /** @var DecidesCollectionPipelineConditionInterface $callback */
        $callback = $this->resolveCallback(DecidesCollectionPipelineConditionInterface::class, $context);

        return $this->passes($callback, $items);
    }

    protected function resolveCallback(string $expected, ?CreationContext $context = null): object
    {
        $resolved = $context?->collectionCallback($this->callback);

        if (!is_object($resolved) && class_exists($this->callback)) {
            try {
                $resolved = resolve($this->callback);
            } catch (Throwable) {
                $resolved = new $this->callback();
            }
        }

        if ($resolved instanceof $expected) {
            return $resolved;
        }

        throw InvalidCollectionAttributeException::forInvalidCallback(
            static::class,
            $this->callback,
            $expected,
        );
    }

    /**
     * @param Collection<array-key, mixed> $items
     */
    abstract protected function passes(DecidesCollectionPipelineConditionInterface $callback, Collection $items): bool;
}
