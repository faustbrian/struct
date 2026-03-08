<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\ValidationAttributes;

use Attribute;
use Cline\Struct\Attributes\AbstractValidationRuleAttribute;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final readonly class CurrentPassword extends AbstractValidationRuleAttribute
{
    public function __construct(
        public ?string $guard = null,
    ) {}

    public function rules(): array
    {
        return $this->guard === null
            ? ['current_password']
            : ['current_password:'.$this->guard];
    }
}
