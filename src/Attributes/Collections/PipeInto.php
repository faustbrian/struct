<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\Collections;

use Attribute;
use Cline\Struct\Contracts\PipesCollectionValueInterface;
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
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class PipeInto implements \Cline\Struct\Contracts\TransformsLaravelCollectionValueInterface
{
    /**
     * @param class-string $class
     */
    public function __construct(
        public string $class,
    ) {}

    public function transformCollection(Collection $items, ?CreationContext $context = null): Collection
    {
        $resolved = $context?->collectionCallback($this->class);

        if (!is_object($resolved) && class_exists($this->class)) {
            try {
                $resolved = resolve($this->class);
            } catch (Throwable) {
                $resolved = new $this->class();
            }
        }

        if (!$resolved instanceof PipesCollectionValueInterface) {
            throw InvalidCollectionAttributeException::forInvalidCallback(
                self::class,
                $this->class,
                PipesCollectionValueInterface::class,
            );
        }

        return $resolved->pipe($items);
    }
}
