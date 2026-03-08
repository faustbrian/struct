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
 * Overrides the serialized output key for a property.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class MapOutputName
{
    /**
     * @param string $name Output key that should be used when serializing the property.
     */
    public function __construct(
        public string $name,
    ) {}
}
