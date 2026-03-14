<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes;

use Attribute;
use Cline\Struct\Contracts\CastInterface;
use Cline\Struct\Enums\DataListType;

/**
 * Declares that a property should hydrate into a typed Laravel collection.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class AsCollection
{
    /**
     * @param class-string|class-string<CastInterface>|DataListType $descriptor Item type or cast descriptor for the collection.
     */
    public function __construct(
        public string|DataListType $descriptor,
    ) {}
}
