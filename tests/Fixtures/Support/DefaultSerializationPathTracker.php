<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class DefaultSerializationPathTracker
{
    public static int $defaultCalls = 0;

    public static int $genericCalls = 0;

    public static function reset(): void
    {
        self::$defaultCalls = 0;
        self::$genericCalls = 0;
    }
}
