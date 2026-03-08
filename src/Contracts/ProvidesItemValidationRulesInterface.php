<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

/**
 * Provides validation rules for each item in a collection-like property.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface ProvidesItemValidationRulesInterface
{
    /**
     * Return the rules that should be applied to each collection item.
     *
     * @return array<int, mixed>
     */
    public function rules(): array;
}
