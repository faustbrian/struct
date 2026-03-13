<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

/**
 * Declares which cast class should be applied for an attribute-backed property transform.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface ProvidesCastClassInterface
{
    /**
     * @return class-string<CastInterface>
     */
    public function castClass(): string;
}
