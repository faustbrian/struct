<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes;

use Attribute;
use Cline\Struct\Factories\AbstractFactory;

/**
 * Assigns a factory implementation to a data object.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class UseFactory
{
    /**
     * @param class-string<AbstractFactory> $factory Factory class to resolve for object creation.
     */
    public function __construct(
        public string $factory,
    ) {}
}
