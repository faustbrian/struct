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
use Cline\Struct\Attributes\Collections\OnlyKeys;
use Cline\Struct\Attributes\Collections\RejectNulls;
use Cline\Struct\Attributes\Collections\Reverse;
use Cline\Struct\Attributes\Collections\Values;
use Cline\Struct\Enums\DataListType;
use Illuminate\Support\Collection;
use Tests\Fixtures\Casts\IntegerStringCast;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class LaravelCollectionAttributeData extends AbstractData
{
    public function __construct(
        #[AsCollection(DataListType::String)]
        #[Reverse()]
        public Collection $reversed,
        #[AsCollection(DataListType::Int)]
        #[Values()]
        public Collection $numbers,
        #[AsCollection(DataListType::String)]
        #[OnlyKeys(['keep', 'also'])]
        public Collection $onlyKeys,
        #[AsCollection(DataListType::String)]
        #[RejectNulls()]
        public Collection $cleaned,
        #[AsCollection(IntegerStringCast::class)]
        public Collection $casted,
        #[AsCollection(SongData::class)]
        public Collection $songs,
    ) {}
}
