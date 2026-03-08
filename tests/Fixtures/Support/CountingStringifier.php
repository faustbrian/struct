<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support;

use Cline\Struct\Contracts\DataObjectInterface;
use Cline\Struct\Contracts\StringifierInterface;
use Cline\Struct\Serialization\SerializationOptions;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class CountingStringifier implements StringifierInterface
{
    public static int $instances = 0;

    public function __construct()
    {
        ++self::$instances;
    }

    public function stringify(
        DataObjectInterface $dto,
        ?SerializationOptions $options = null,
    ): string {
        return $dto->toJson(serialization: $options);
    }
}
