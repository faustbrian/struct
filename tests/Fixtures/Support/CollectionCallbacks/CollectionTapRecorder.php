<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support\CollectionCallbacks;

use Cline\Struct\Contracts\TapsCollectionValueInterface;
use Illuminate\Support\Collection;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class CollectionTapRecorder implements TapsCollectionValueInterface
{
    /** @var list<array<array-key, mixed>> */
    public array $snapshots = [];

    public function tap(Collection $items): void
    {
        $this->snapshots[] = $items->values()->all();
    }
}
