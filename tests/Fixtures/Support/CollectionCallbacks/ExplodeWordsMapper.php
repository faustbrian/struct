<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support\CollectionCallbacks;

use Cline\Struct\Contracts\MapsCollectionItemsInterface;

use function explode;
use function is_string;
use function mb_strtolower;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class ExplodeWordsMapper implements MapsCollectionItemsInterface
{
    public function map(mixed $value, int|string $key): mixed
    {
        return is_string($value)
            ? explode(' ', mb_strtolower($value))
            : [$value];
    }
}
