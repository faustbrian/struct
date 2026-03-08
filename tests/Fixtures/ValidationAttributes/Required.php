<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\ValidationAttributes;

use Attribute;
use Cline\Struct\Contracts\ProvidesValidationRulesInterface;

/**
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class Required implements ProvidesValidationRulesInterface
{
    public function rules(): array
    {
        return ['required'];
    }
}
