<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Validation;

use Illuminate\Contracts\Validation\ValidationRule;

use function array_filter;
use function array_map;
use function array_values;
use function explode;
use function is_string;
use function is_subclass_of;

/**
 * Normalizes user-defined rule definitions into a single flattened format.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class RuleSetNormalizer
{
    /**
     * Converts a pipe-separated string or rule array to a normalized rule list.
     *
     * String rules are split on `|` and trimmed. Rule class strings are
     * instantiated using the class name so they can be reused by Laravel.
     *
     * The output always contains only concrete entries accepted by the validator.
     * while preserving declaration order.
     *
     * @param  array<int, mixed>|string $ruleset
     * @return array<int, mixed>
     */
    public function normalize(string|array $ruleset): array
    {
        if (is_string($ruleset)) {
            return array_values(array_filter(array_map(trim(...), explode('|', $ruleset))));
        }

        $rules = [];

        foreach ($ruleset as $rule) {
            if (is_string($rule)) {
                if (is_subclass_of($rule, ValidationRule::class)) {
                    $rules[] = new $rule();

                    continue;
                }

                foreach (array_values(array_filter(array_map(trim(...), explode('|', $rule)))) as $normalizedRule) {
                    $rules[] = $normalizedRule;
                }

                continue;
            }

            $rules[] = $rule;
        }

        return $rules;
    }
}
