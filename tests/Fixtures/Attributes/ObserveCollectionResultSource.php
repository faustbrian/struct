<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Attributes;

use Attribute;
use Cline\Struct\Contracts\ComputesCollectionResultValueInterface;
use Cline\Struct\Support\CreationContext;
use Illuminate\Support\Collection;

use function spl_object_id;

/**
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class ObserveCollectionResultSource implements ComputesCollectionResultValueInterface
{
    public function __construct(
        private string $source,
    ) {}

    public function sourceProperty(): string
    {
        return $this->source;
    }

    public function computeResult(Collection $items, array $properties, ?CreationContext $context = null): int
    {
        return spl_object_id($items);
    }
}
