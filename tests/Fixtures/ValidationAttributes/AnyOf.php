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
use Illuminate\Validation\Rule;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final readonly class AnyOf extends AbstractValidationRuleAttribute
{
    /**
     * @param array<int, array<int, mixed>|mixed> $rulesets
     */
    public function __construct(
        public array $rulesets,
    ) {}

    public function rules(): array
    {
        return [Rule::anyOf($this->rulesets)];
    }
}
