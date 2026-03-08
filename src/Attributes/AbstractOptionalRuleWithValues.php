<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes;

/**
 * Validation rule attribute backed by an optional list of parameters.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
abstract readonly class AbstractOptionalRuleWithValues extends AbstractValidationRuleAttribute
{
    /**
     * @param array<int, mixed> $values
     */
    public function __construct(
        public array $values = [],
    ) {}

    /**
     * Build Laravel-style rule strings from the optional value list.
     *
     * @return array<int, string>
     */
    public function rules(): array
    {
        return $this->values === []
            ? static::rule(static::name())
            : static::rule(static::name(), ...$this->values);
    }

    /**
     * Return the validator rule key implemented by the concrete attribute.
     */
    abstract protected static function name(): string;
}
