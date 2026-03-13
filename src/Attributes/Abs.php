<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes;

use Attribute;
use Cline\Struct\Casts\NumericCast;
use Cline\Struct\Contracts\ProvidesCastClassInterface;

/**
 * Converts a numeric property to its absolute value.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Abs implements ProvidesCastClassInterface
{
    public function castClass(): string
    {
        return NumericCast::class;
    }
}
