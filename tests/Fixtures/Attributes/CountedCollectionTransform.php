<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Attributes;

use Attribute;
use Cline\Struct\Attributes\Collections\AbstractCollectionTransformer;
use Tests\Fixtures\Support\ObservedCollectionTransformInstantiations;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class CountedCollectionTransform extends AbstractCollectionTransformer
{
    public function __construct()
    {
        ++ObservedCollectionTransformInstantiations::$count;
    }

    public function transform(array $items): array
    {
        return $items;
    }
}
