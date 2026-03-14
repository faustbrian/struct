<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support\CollectionCallbacks;

use Cline\Struct\Contracts\MapsCollectionItemsWithKeysInterface;

use function is_string;
use function mb_strtolower;
use function mb_substr;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class LowercaseKeyValueMap implements MapsCollectionItemsWithKeysInterface
{
    /**
     * @return array<string, mixed>
     */
    public function mapWithKeys(mixed $value, int|string $key): array
    {
        if (!is_string($value)) {
            return [(string) $key => $value];
        }

        return [mb_strtolower(mb_substr($value, 0, 1)) => $value];
    }
}
