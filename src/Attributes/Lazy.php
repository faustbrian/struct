<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes;

use Attribute;
use Cline\Struct\Contracts\ComputesValueInterface;
use Cline\Struct\Contracts\ResolvesLazyValueInterface;

/**
 * Marks a property for lazy resolution during serialization or access.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Lazy
{
    /**
     * @param null|class-string<ComputesValueInterface>|class-string<ResolvesLazyValueInterface> $resolver Resolver used to compute or resolve the lazy value.
     */
    public function __construct(
        public ?string $resolver = null,
    ) {}
}
