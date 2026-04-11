<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\AsDataList;
use Cline\Struct\Attributes\ValidateItems;
use Cline\Struct\Enums\DataListType;
use Cline\Struct\Support\DataList;
use Tests\Fixtures\Rules\UppercaseValueRule;

/**
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
final readonly class ItemValidatedData extends AbstractData
{
    public function __construct(
        #[ValidateItems('min:10')]
        #[AsDataList(DataListType::Int)]
        public DataList $scores,
        #[ValidateItems([UppercaseValueRule::class])]
        public array $codes,
    ) {}
}
