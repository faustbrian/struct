<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

/**
 * Declares a deterministic string transformation for attribute-backed casts.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface TransformsStringValueInterface
{
    public function transform(string $value): string;
}
