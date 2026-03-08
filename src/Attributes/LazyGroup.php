<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes;

use Attribute;

/**
 * Assigns a lazy property to one or more serialization groups.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final readonly class LazyGroup
{
    /**
     * @param string $group Lazy group name required to include the property.
     */
    public function __construct(
        public string $group,
    ) {}
}
