<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes;

/**
 * Validation rule attribute that accepts a single parameter value.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
abstract readonly class AbstractRuleWithValue extends AbstractValidationRuleAttribute
{
    /**
     * @param mixed $value The rule parameter value.
     */
    public function __construct(
        public mixed $value,
    ) {}

    /**
     * Build a Laravel-style rule string using the configured value.
     *
     * @return array<int, string>
     */
    public function rules(): array
    {
        return static::rule(static::name(), $this->value);
    }

    /**
     * Return the validator rule key implemented by the concrete attribute.
     */
    abstract protected static function name(): string;
}
