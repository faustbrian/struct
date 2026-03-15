<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

use Cline\Struct\Metadata\PropertyMetadata;
use Cline\Struct\Support\PropertyHydrationContext;

/**
 * Declares a cast that can inspect whole-DTO hydration context.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface ContextualCastInterface extends CastInterface
{
    public function getWithContext(
        PropertyMetadata $property,
        mixed $value,
        PropertyHydrationContext $context,
    ): mixed;
}
