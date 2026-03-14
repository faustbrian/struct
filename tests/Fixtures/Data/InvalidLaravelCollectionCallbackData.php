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
use Cline\Struct\Attributes\Collections\Filter;
use Cline\Struct\Attributes\Collections\Map;
use Cline\Struct\Enums\DataListType;
use Illuminate\Support\Collection;
use Tests\Fixtures\Support\CollectionCallbacks\EvenNumberPredicate;
use Tests\Fixtures\Support\CollectionCallbacks\RecordValueAction;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class InvalidLaravelCollectionCallbackData extends AbstractData
{
    public function __construct(
        #[Filter(EvenNumberPredicate::class)]
        public array $arrayValues,
        #[AsCollection(DataListType::String)]
        #[Map(RecordValueAction::class)]
        public Collection $invalidMapper,
    ) {}
}
