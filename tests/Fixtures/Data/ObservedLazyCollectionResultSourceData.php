<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\AsLazyCollection;
use Cline\Struct\Enums\DataListType;
use Illuminate\Support\LazyCollection;
use Tests\Fixtures\Attributes\ObserveCollectionResultSource;
use Tests\Fixtures\Support\ObservedCollectionResultSources;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class ObservedLazyCollectionResultSourceData extends AbstractData
{
    public static function reset(): void
    {
        ObservedCollectionResultSources::$ids = [];
    }

    public function __construct(
        #[AsLazyCollection(DataListType::String)]
        public LazyCollection $items,
        #[ObserveCollectionResultSource('items')]
        public int $firstObservation,
        #[ObserveCollectionResultSource('items')]
        public int $secondObservation,
    ) {
        ObservedCollectionResultSources::$ids = [
            $this->firstObservation,
            $this->secondObservation,
        ];
    }
}
