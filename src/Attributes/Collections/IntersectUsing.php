<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\Collections;

use Attribute;
use Cline\Struct\Contracts\ComparesCollectionValuesInterface;
use Cline\Struct\Support\CreationContext;
use Illuminate\Support\Collection;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class IntersectUsing extends AbstractCollectionOperandTransformer
{
    /**
     * @param class-string                 $callback
     * @param null|array<array-key, mixed> $values
     */
    public function __construct(
        public string $callback,
        ?string $source = null,
        ?array $values = null,
    ) {
        parent::__construct($source, $values);
    }

    public function transformCollection(Collection $items, ?CreationContext $context = null): Collection
    {
        /** @var ComparesCollectionValuesInterface $callback */
        $callback = $this->resolveCallback($this->callback, ComparesCollectionValuesInterface::class, $context);

        return $items->intersectUsing(
            $this->resolveOperand($context),
            static fn (mixed $left, mixed $right): int => $callback->compare($left, $right),
        );
    }
}
