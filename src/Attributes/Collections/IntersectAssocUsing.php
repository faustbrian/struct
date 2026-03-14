<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\Collections;

use Attribute;
use Cline\Struct\Contracts\ComparesCollectionKeysInterface;
use Cline\Struct\Support\CreationContext;
use Illuminate\Support\Collection;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class IntersectAssocUsing extends AbstractCollectionOperandTransformer
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
        /** @var ComparesCollectionKeysInterface $callback */
        $callback = $this->resolveCallback($this->callback, ComparesCollectionKeysInterface::class, $context);

        return $items->intersectAssocUsing(
            $this->resolveOperand($context),
            static fn (int|string $left, int|string $right): int => $callback->compare($left, $right),
        );
    }
}
