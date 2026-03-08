<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes;

/**
 * Validation rule attribute that accepts a pair of positional parameters.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
abstract readonly class AbstractRuleWithPair extends AbstractValidationRuleAttribute
{
    /**
     * @param mixed $first  The first rule parameter.
     * @param mixed $second The second rule parameter.
     */
    public function __construct(
        public mixed $first,
        public mixed $second,
    ) {}

    /**
     * Build a Laravel-style rule string using both configured values.
     *
     * @return array<int, string>
     */
    public function rules(): array
    {
        return static::rule(static::name(), $this->first, $this->second);
    }

    /**
     * Return the validator rule key implemented by the concrete attribute.
     */
    abstract protected static function name(): string;
}
