<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support\CollectionCallbacks;

use Cline\Struct\Contracts\MapsCollectionItemsWithKeysInterface;

use function mb_strtolower;
use function mb_substr;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class FirstLetterKeyValueMap implements MapsCollectionItemsWithKeysInterface
{
    public function mapWithKeys(mixed $value, int|string $key): array
    {
        return [mb_substr(mb_strtolower((string) $value), 0, 1) => $value];
    }
}
