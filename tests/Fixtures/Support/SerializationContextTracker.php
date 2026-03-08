<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support;

use Cline\Struct\Serialization\SerializationContext;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class SerializationContextTracker
{
    /** @var list<SerializationContext> */
    public static array $seen = [];
}
