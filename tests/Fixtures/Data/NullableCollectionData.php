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
use Cline\Struct\Attributes\AsDataCollection;
use Cline\Struct\Attributes\AsDataList;
use Cline\Struct\Attributes\AsLazyCollection;
use Cline\Struct\Attributes\AsLazyDataCollection;
use Cline\Struct\Attributes\AsLazyDataList;
use Cline\Struct\Support\DataCollection;
use Cline\Struct\Support\DataList;
use Cline\Struct\Support\LazyDataCollection;
use Cline\Struct\Support\LazyDataList;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Tests\Fixtures\Casts\IntegerStringCast;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class NullableCollectionData extends AbstractData
{
    public function __construct(
        #[AsDataList(IntegerStringCast::class)]
        public ?DataList $numbers = null,
        #[AsDataCollection(SongData::class)]
        public ?DataCollection $songs = null,
        #[AsLazyDataList(IntegerStringCast::class)]
        public ?LazyDataList $lazyNumbers = null,
        #[AsLazyDataCollection(SongData::class)]
        public ?LazyDataCollection $lazySongs = null,
        #[AsCollection(IntegerStringCast::class)]
        public ?Collection $collectionNumbers = null,
        #[AsLazyCollection(IntegerStringCast::class)]
        public ?LazyCollection $lazyCollectionNumbers = null,
    ) {}
}
