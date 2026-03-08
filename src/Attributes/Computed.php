<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes;

use Attribute;
use Cline\Struct\Contracts\ComputesValueInterface;

/**
 * Marks a property as computed instead of being read from the payload.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Computed
{
    /**
     * @param null|class-string<ComputesValueInterface> $computer Optional computer class used to resolve the property value.
     */
    public function __construct(
        public ?string $computer = null,
    ) {}
}
