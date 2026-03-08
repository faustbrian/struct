<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes;

use Attribute;
use Cline\Struct\Enums\NameMapper;

/**
 * Applies a name-mapping strategy to both input and output property names.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
final readonly class MapName
{
    /**
     * @param NameMapper $mapper Mapper used to transform the property name.
     */
    public function __construct(
        public NameMapper $mapper,
    ) {}
}
