<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

use Illuminate\Support\Collection;

/**
 * Decides whether the next collection transform should run.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface DecidesCollectionPipelineConditionInterface
{
    public function shouldApply(Collection $items): bool;
}
