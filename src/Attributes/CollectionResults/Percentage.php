<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\CollectionResults;

use Attribute;
use Cline\Struct\Contracts\FiltersCollectionItemsInterface;
use Cline\Struct\Support\CreationContext;
use Illuminate\Support\Collection;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Percentage extends AbstractCollectionResultAttribute
{
    /**
     * @param class-string $callback
     */
    public function __construct(
        string $source,
        public string $callback,
        public int $precision = 2,
    ) {
        parent::__construct($source);
    }

    public function computeResult(Collection $items, array $properties, ?CreationContext $context = null): mixed
    {
        /** @var FiltersCollectionItemsInterface $callback */
        $callback = $this->resolveCallback($this->callback, FiltersCollectionItemsInterface::class, $context);

        return $items->percentage(
            static fn (mixed $value, int|string $key): bool => $callback->passes($value, $key),
            $this->precision,
        );
    }
}
