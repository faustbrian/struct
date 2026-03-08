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
 * Rejects unknown input keys during hydration.
 *
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class ForbidSuperfluousKeys {}
