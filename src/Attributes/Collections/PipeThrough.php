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
use Cline\Struct\Contracts\TransformsLaravelCollectionValueInterface;
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
final readonly class PipeThrough implements TransformsLaravelCollectionValueInterface
{
    /**
     * @param array<array-key, class-string> $callbacks
     */
    public function __construct(
        public array $callbacks,
    ) {}

    public function transformCollection(Collection $items, ?CreationContext $context = null): Collection
    {
        foreach ($this->callbacks as $class) {
            $resolved = $context?->collectionCallback($class);

            if (!is_object($resolved) && class_exists($class)) {
                try {
                    $resolved = resolve($class);
                } catch (Throwable) {
                    $resolved = new $class();
                }
            }

            if (!$resolved instanceof PipesCollectionValueInterface) {
                throw InvalidCollectionAttributeException::forInvalidCallback(
                    self::class,
                    $class,
                    PipesCollectionValueInterface::class,
                );
            }

            $items = $resolved->pipe($items);
        }

        return $items;
    }
}
