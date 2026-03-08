<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

/**
 * Provides validation rules for a property.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface ProvidesValidationRulesInterface
{
    /**
     * Return the rules that should be applied to the property.
     *
     * @return array<int, mixed>
     */
    public function rules(): array;
}
