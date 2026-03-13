<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes;

use Cline\Struct\Casts\StringCast;
use Cline\Struct\Contracts\ProvidesCastClassInterface;
use Cline\Struct\Contracts\TransformsStringValueInterface;

/**
 * Base attribute for string transformations applied during hydration and serialization.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
abstract readonly class AbstractStringTransformer implements ProvidesCastClassInterface, TransformsStringValueInterface
{
    public function castClass(): string
    {
        return StringCast::class;
    }
}
