<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes;

use Attribute;
use Cline\Struct\Contracts\SerializationConditionInterface;

/**
 * Excludes a property from serialization when a condition matches.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final readonly class ExcludeWhen
{
    /**
     * @param class-string<SerializationConditionInterface> $condition Condition class that decides exclusion.
     */
    public function __construct(
        public string $condition,
    ) {}
}
