<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes;

use Attribute;
use Cline\Struct\Contracts\ModelPayloadResolverInterface;

/**
 * Assigns a model payload resolver to a data object.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class UseModelPayloadResolver
{
    /**
     * @param class-string<ModelPayloadResolverInterface> $resolver Resolver class to read model data.
     */
    public function __construct(
        public string $resolver,
    ) {}
}
