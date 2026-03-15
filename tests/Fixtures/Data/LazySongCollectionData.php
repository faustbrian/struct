<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\AsLazyDataCollection;
use Cline\Struct\Support\LazyDataCollection;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class LazySongCollectionData extends AbstractData
{
    public function __construct(
        #[AsLazyDataCollection(SongData::class)]
        public LazyDataCollection $songs,
    ) {}
}
