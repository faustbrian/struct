<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes;

use Attribute;
use Cline\Struct\Contracts\ProvidesValidationRulesInterface;
use Cline\Struct\Validation\RuleSetNormalizer;

/**
 * Adds validation rules for a property.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final readonly class Validate implements ProvidesValidationRulesInterface
{
    /**
     * @param array<int, mixed>|string $ruleset Validation rules to normalize for the property.
     */
    public function __construct(
        public string|array $ruleset,
    ) {}

    /**
     * Normalize the configured rules into Laravel's array form.
     *
     * @return array<int, mixed>
     */
    public function rules(): array
    {
        return new RuleSetNormalizer()->normalize($this->ruleset);
    }
}
