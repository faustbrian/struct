<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\AsCollection;
use Cline\Struct\Attributes\Collections\Map;
use Cline\Struct\Enums\DataListType;
use Illuminate\Support\Collection;
use Tests\Fixtures\Support\CollectionCallbacks\BoundPrefixMapper;
use Tests\Fixtures\Support\CollectionCallbacks\CountingPassthroughMapper;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class LaravelCollectionCallbackReuseData extends AbstractData
{
    public function __construct(
        #[AsCollection(DataListType::String)]
        #[Map(CountingPassthroughMapper::class)]
        public Collection $directFirst,
        #[AsCollection(DataListType::String)]
        #[Map(CountingPassthroughMapper::class)]
        public Collection $directSecond,
        #[AsCollection(DataListType::String)]
        #[Map(BoundPrefixMapper::class)]
        public Collection $boundFirst,
        #[AsCollection(DataListType::String)]
        #[Map(BoundPrefixMapper::class)]
        public Collection $boundSecond,
    ) {}
}
