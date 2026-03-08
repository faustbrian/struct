<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

use Cline\Struct\Metadata\ClassMetadata;
use Illuminate\Validation\Validator;

/**
 * Customizes the validator created for a data object payload.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface ValidatorMutatorInterface
{
    /**
     * Return additional rules that should be merged into inferred rules.
     *
     * @param  array<string, mixed>             $input
     * @return array<string, array<int, mixed>>
     */
    public function rules(array $input, ClassMetadata $metadata): array;

    /**
     * Return custom validation error messages for the current payload.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, string>
     */
    public function messages(array $input, ClassMetadata $metadata): array;

    /**
     * Return custom validation attribute labels for the current payload.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, string>
     */
    public function attributes(array $input, ClassMetadata $metadata): array;

    /**
     * Determine whether Laravel should stop on the first validation failure.
     *
     * @param array<string, mixed> $input
     */
    public function stopOnFirstFailure(array $input, ClassMetadata $metadata): bool;

    /**
     * Return the validation error bag name for the current payload.
     *
     * @param array<string, mixed> $input
     */
    public function errorBag(array $input, ClassMetadata $metadata): ?string;

    /**
     * Customize the validator after it has been created.
     *
     * @param array<string, mixed> $input
     */
    public function withValidator(Validator $validator, array $input, ClassMetadata $metadata): void;
}
