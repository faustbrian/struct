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
use Cline\Struct\Contracts\ContextualTransformsCollectionValueInterface;
use Cline\Struct\Support\PropertyHydrationContext;

use function is_scalar;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class PrefixCollectionBySibling extends AbstractCollectionTransformer implements ContextualTransformsCollectionValueInterface
{
    /**
     * @param  array<array-key, mixed> $items
     * @return array<array-key, mixed>
     */
    public function transform(array $items): array
    {
        return $items;
    }

    /**
     * @param  array<array-key, mixed> $items
     * @return array<array-key, mixed>
     */
    public function transformWithContext(array $items, PropertyHydrationContext $context): array
    {
        $prefix = (string) ($context->resolvedProperties['prefix'] ?? '');

        foreach ($items as $key => $value) {
            if (!is_scalar($value)) {
                continue;
            }

            $items[$key] = $prefix.$value;
        }

        return $items;
    }
}
