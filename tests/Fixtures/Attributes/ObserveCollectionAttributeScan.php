<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Attributes;

use Attribute;
use Tests\Fixtures\Support\ObservedCollectionAttributeScans;

/**
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class ObserveCollectionAttributeScan
{
    public function __construct()
    {
        ++ObservedCollectionAttributeScans::$count;
    }
}
