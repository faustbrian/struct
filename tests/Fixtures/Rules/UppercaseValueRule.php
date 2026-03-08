<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

use function is_string;
use function mb_strtoupper;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class UppercaseValueRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || mb_strtoupper($value) === $value) {
            return;
        }

        $fail('The :attribute must be uppercase.');
    }
}
