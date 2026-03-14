<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support\CollectionCallbacks;

use Cline\Struct\Contracts\SpreadsCollectionItemsInterface;

use function array_map;
use function implode;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class SpreadRecorder implements SpreadsCollectionItemsInterface
{
    /** @var list<list<mixed>> */
    public array $calls = [];

    public function spread(mixed ...$values): mixed
    {
        $this->calls[] = $values;

        return implode(':', array_map(static fn (mixed $value): string => (string) $value, $values));
    }
}
