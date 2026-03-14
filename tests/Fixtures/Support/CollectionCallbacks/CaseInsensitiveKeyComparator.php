<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support\CollectionCallbacks;

use Cline\Struct\Contracts\ComparesCollectionKeysInterface;

use function strcasecmp;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class CaseInsensitiveKeyComparator implements ComparesCollectionKeysInterface
{
    public function compare(int|string $left, int|string $right): int
    {
        return strcasecmp((string) $left, (string) $right);
    }
}
