<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Support;

use Cline\Struct\Contracts\StringifierInterface;
use Throwable;

use function resolve;

/**
 * Reuses resolved stringifier instances across normal application use.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @internal
 */
final class StringifierResolver
{
    /** @var array<class-string<StringifierInterface>, StringifierInterface> */
    private array $instances = [];

    /**
     * @param null|class-string<StringifierInterface> $class
     */
    public function resolve(?string $class): ?StringifierInterface
    {
        if ($class === null) {
            return null;
        }

        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }

        try {
            /** @var StringifierInterface $resolved */
            $resolved = resolve($class);
        } catch (Throwable) {
            $resolved = new $class();
        }

        return $this->instances[$class] = $resolved;
    }
}
