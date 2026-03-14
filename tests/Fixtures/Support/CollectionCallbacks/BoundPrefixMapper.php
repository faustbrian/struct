<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support\CollectionCallbacks;

use Cline\Struct\Contracts\MapsCollectionItemsInterface;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class BoundPrefixMapper implements MapsCollectionItemsInterface
{
    public function __construct(
        private string $prefix,
    ) {}

    public function map(mixed $value, int|string $key): mixed
    {
        return $this->prefix.$value;
    }
}
