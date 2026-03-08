<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Exceptions;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Wraps request validation failures raised while creating a data object.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class RequestDataValidationException extends ValidationException implements StructException
{
    public static function fromValidator(Validator $validator, ?string $errorBag = null): self
    {
        return new self(
            $validator,
            errorBag: $errorBag ?? 'default',
        );
    }
}
