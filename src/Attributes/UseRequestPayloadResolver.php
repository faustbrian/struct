<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes;

use Attribute;
use Cline\Struct\Contracts\RequestPayloadResolverInterface;

/**
 * Assigns a request payload resolver to a data object.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class UseRequestPayloadResolver
{
    /**
     * @param class-string<RequestPayloadResolverInterface> $resolver Resolver class to read request input.
     */
    public function __construct(
        public string $resolver,
    ) {}
}
