<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Tests\Fixtures\Attributes\CountedCollectionTransform;
use Tests\Fixtures\Support\ObservedCollectionTransformInstantiations;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class ObservedCollectionTransformData extends AbstractData
{
    public static function reset(): void
    {
        ObservedCollectionTransformInstantiations::$count = 0;
    }

    public function __construct(
        #[CountedCollectionTransform()]
        public array $items,
    ) {}
}
