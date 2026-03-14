<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support\CollectionCallbacks;

use Cline\Struct\Contracts\MapsCollectionItemsInterface;

use function is_string;
use function mb_strtoupper;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class KeyAwareUppercaseMapper implements MapsCollectionItemsInterface
{
    public function map(mixed $value, int|string $key): mixed
    {
        return $key.':'.(is_string($value) ? mb_strtoupper($value) : $value);
    }
}
