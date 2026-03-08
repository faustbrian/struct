<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Serialization;

use LogicException;
use ReflectionClass;

use function app;
use function array_key_exists;
use function class_exists;
use function function_exists;
use function is_object;
use function resolve;

/**
 * Caches helper objects for one serialization pass.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @internal
 */
final class SerializableResolverCache
{
    /** @var array<string, false|object> */
    private array $instances = [];

    /** @var array<string, 'container'|'direct'|'invalid'> */
    private array $strategies = [];

    public function resolve(?string $class): ?object
    {
        if ($class === null) {
            return null;
        }

        if (array_key_exists($class, $this->instances)) {
            return $this->instances[$class] ?: null;
        }

        if (!class_exists($class)) {
            $this->strategies[$class] = 'invalid';
            $this->instances[$class] = false;

            return null;
        }

        $strategy = $this->strategies[$class] ?? $this->strategyFor($class);

        if ($strategy === 'direct') {
            $resolved = new $class();
        } elseif ($strategy === 'container') {
            $resolved = $this->resolveFromContainer($class);
        } else {
            $this->instances[$class] = false;

            return null;
        }

        return $this->instances[$class] = $resolved;
    }

    /**
     * @param class-string $class
     */
    private function strategyFor(string $class): string
    {
        if ($this->canInstantiateDirectly($class)) {
            return $this->strategies[$class] = 'direct';
        }

        if ($this->isBound($class)) {
            return $this->strategies[$class] = 'container';
        }

        return $this->strategies[$class] = 'invalid';
    }

    /**
     * @param class-string $class
     */
    private function canInstantiateDirectly(string $class): bool
    {
        if ($this->isBound($class)) {
            return false;
        }

        $reflection = new ReflectionClass($class);

        $constructor = $reflection->getConstructor();

        return $constructor === null || $constructor->getNumberOfRequiredParameters() === 0;
    }

    /**
     * @param class-string $class
     */
    private function resolveFromContainer(string $class): object
    {
        $resolved = resolve($class);

        return is_object($resolved)
            ? $resolved
            : throw new LogicException('Resolved helper is not an object.');
    }

    /**
     * @param class-string $class
     */
    private function isBound(string $class): bool
    {
        return function_exists('app') && app()->bound($class);
    }
}
