<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\CollectionResults;

use Cline\Struct\Contracts\ComputesCollectionResultValueInterface;
use Cline\Struct\Exceptions\InvalidCollectionAttributeException;
use Cline\Struct\Support\CreationContext;
use Throwable;

use function class_exists;
use function is_object;
use function resolve;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
abstract readonly class AbstractCollectionResultAttribute implements ComputesCollectionResultValueInterface
{
    public function __construct(
        public string $source,
    ) {}

    public function sourceProperty(): string
    {
        return $this->source;
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
