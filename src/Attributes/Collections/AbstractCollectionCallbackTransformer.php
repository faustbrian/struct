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
use Throwable;

use function class_exists;
use function is_object;
use function resolve;

/**
 * Base attribute for callback-driven Laravel collection transforms.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
abstract readonly class AbstractCollectionCallbackTransformer implements TransformsLaravelCollectionValueInterface
{
    /**
     * @param class-string $callback
     */
    public function __construct(
        public string $callback,
    ) {}

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
}
