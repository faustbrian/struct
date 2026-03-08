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
final readonly class Exists extends AbstractValidationRuleAttribute
{
    public function __construct(
        public string $table,
        public string $column = 'NULL',
    ) {}

    public function rules(): array
    {
        return [Rule::exists($this->table, $this->column)];
    }
}
