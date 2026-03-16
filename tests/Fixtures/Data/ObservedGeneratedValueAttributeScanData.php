<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Tests\Fixtures\Attributes\ObserveCollectionAttributeScan;
use Tests\Fixtures\Support\ObservedCollectionAttributeScans;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class ObservedGeneratedValueAttributeScanData extends AbstractData
{
    public function __construct(
        #[ObserveCollectionAttributeScan()]
        public string $name,
    ) {}

    public static function reset(): void
    {
        ObservedCollectionAttributeScans::$count = 0;
    }
}
