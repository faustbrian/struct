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

use function is_numeric;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class IntegerStringCast implements CastInterface
{
    public function get(PropertyMetadata $property, mixed $value): mixed
    {
        return is_numeric($value) ? (int) $value : $value;
    }

    public function set(PropertyMetadata $property, mixed $value): mixed
    {
        return is_numeric($value) ? (string) (int) $value : $value;
    }
}
