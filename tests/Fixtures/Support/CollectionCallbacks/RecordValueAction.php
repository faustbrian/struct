<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support\CollectionCallbacks;

use Cline\Struct\Contracts\PerformsCollectionActionInterface;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class RecordValueAction implements PerformsCollectionActionInterface
{
    /** @var list<string> */
    public static array $calls = [];

    public function execute(mixed $value, int|string $key): void
    {
        self::$calls[] = $key.':'.$value;
    }
}
