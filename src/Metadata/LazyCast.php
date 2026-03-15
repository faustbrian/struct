<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Metadata;

use Cline\Struct\Contracts\CastInterface;
use Cline\Struct\Contracts\ContextualCastInterface;
use Cline\Struct\Support\PropertyHydrationContext;

/**
 * Defers cast construction until a cached metadata entry actually uses it.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @internal
 */
final class LazyCast implements CastInterface, ContextualCastInterface
{
    private ?CastInterface $instance = null;

    /**
     * @param class-string<CastInterface> $class
     */
    public function __construct(
        private readonly string $class,
    ) {}

    public function get(PropertyMetadata $property, mixed $value): mixed
    {
        return $this->instance()->get($property, $value);
    }

    public function getWithContext(
        PropertyMetadata $property,
        mixed $value,
        PropertyHydrationContext $context,
    ): mixed {
        $instance = $this->instance();

        if ($instance instanceof ContextualCastInterface) {
            return $instance->getWithContext($property, $value, $context);
        }

        return $instance->get($property, $value);
    }

    public function set(PropertyMetadata $property, mixed $value): mixed
    {
        return $this->instance()->set($property, $value);
    }

    private function instance(): CastInterface
    {
        return $this->instance ??= new $this->class();
    }
}
