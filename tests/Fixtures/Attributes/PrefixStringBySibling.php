<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Attributes;

use Attribute;
use Cline\Struct\Attributes\Strings\AbstractStringTransformer;
use Cline\Struct\Contracts\ContextualTransformsStringValueInterface;
use Cline\Struct\Support\PropertyHydrationContext;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class PrefixStringBySibling extends AbstractStringTransformer implements ContextualTransformsStringValueInterface
{
    public function transform(string $value): string
    {
        return $value;
    }

    public function transformWithContext(string $value, PropertyHydrationContext $context): string
    {
        return ($context->resolvedProperties['prefix'] ?? '').$value;
    }
}
