<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support\CollectionCallbacks;

use Cline\Struct\Contracts\ComputesCollectionGroupKeyInterface;

use function is_string;
use function mb_strtolower;
use function mb_substr;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class FirstLetterGroupKey implements ComputesCollectionGroupKeyInterface
{
    public function groupKey(mixed $value, int|string $key): int|string
    {
        return is_string($value) ? mb_strtolower(mb_substr($value, 0, 1)) : $key;
    }
}
