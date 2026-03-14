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
use Cline\Struct\Attributes\Collections\EachSpread;
use Cline\Struct\Attributes\Collections\Ensure;
use Cline\Struct\Attributes\Collections\MapSpread;
use Cline\Struct\Attributes\Collections\Tap;
use Cline\Struct\Attributes\CollectionSources\FromJson;
use Cline\Struct\Enums\DataListType;
use Illuminate\Support\Collection;
use Tests\Fixtures\Support\CollectionCallbacks\CollectionTapRecorder;
use Tests\Fixtures\Support\CollectionCallbacks\SpreadRecorder;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class LaravelCollectionProcessorData extends AbstractData
{
    public function __construct(
        #[AsCollection(DataListType::Array)]
        #[EachSpread(SpreadRecorder::class)]
        public Collection $spreadEach,
        #[AsCollection(DataListType::Array)]
        #[MapSpread(SpreadRecorder::class)]
        public Collection $spreadMapped,
        #[AsCollection(DataListType::String)]
        #[Ensure('string')]
        public Collection $ensuredStrings,
        #[AsCollection(DataListType::String)]
        #[Tap(CollectionTapRecorder::class)]
        public Collection $tapped,
        #[AsCollection(DataListType::Mixed)]
        #[FromJson(source: 'jsonPayload')]
        public Collection $fromJson,
        public string $jsonPayload,
    ) {}
}
