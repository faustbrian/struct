<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes;

/**
 * Validation rule attribute that never accepts additional parameters.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
abstract readonly class AbstractRuleWithoutParameters extends AbstractValidationRuleAttribute
{
    /**
     * Build a single Laravel validation rule with no parameters.
     *
     * @return array<int, string>
     */
    public function rules(): array
    {
        return static::rule(static::name());
    }

    /**
     * Return the validator rule key implemented by the concrete attribute.
     */
    abstract protected static function name(): string;
}
