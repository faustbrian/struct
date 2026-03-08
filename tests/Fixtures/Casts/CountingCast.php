<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Casts;

use Cline\Struct\Contracts\CastInterface;
use Cline\Struct\Metadata\PropertyMetadata;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class CountingCast implements CastInterface
{
    public static int $instances = 0;

    public function __construct()
    {
        ++self::$instances;
    }

    public function get(PropertyMetadata $property, mixed $value): mixed
    {
        return $value;
    }

    public function set(PropertyMetadata $property, mixed $value): mixed
    {
        return $value;
    }
}
