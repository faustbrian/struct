<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Exceptions;

use Illuminate\Contracts\Validation\Validator;

/**
 * Wraps validation failures raised while creating a data object.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class DataValidationException extends AbstractStructException
{
    /**
     * @param array<string, array<int, string>> $errors Validation messages keyed by attribute.
     */
    public function __construct(
        string $message,
        private readonly array $errors = [],
        private readonly ?string $errorBag = null,
    ) {
        parent::__construct($message);
    }

    /**
     * Build the exception from a Laravel validator instance.
     */
    public static function fromValidator(Validator $validator, ?string $errorBag = null): self
    {
        /** @var array<string, array<int, string>> $errors */
        $errors = $validator->errors()->toArray();

        return new self(
            $validator->errors()->first() ?: 'Data validation failed.',
            $errors,
            $errorBag,
        );
    }

    /**
     * Return the validation errors keyed by attribute name.
     *
     * @return array<string, array<int, string>>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Return the configured Laravel error bag, if any.
     */
    public function errorBag(): ?string
    {
        return $this->errorBag;
    }
}
