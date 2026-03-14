<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support\CollectionCallbacks;

use Cline\Struct\Contracts\DecidesCollectionPipelineConditionInterface;
use Illuminate\Support\Collection;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class HasMultipleItemsCondition implements DecidesCollectionPipelineConditionInterface
{
    public function shouldApply(Collection $items): bool
    {
        return $items->count() > 1;
    }
}
