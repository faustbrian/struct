<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Validation;

use Cline\Struct\Metadata\ClassMetadata;
use Cline\Struct\Validation\AbstractValidatorMutator;
use Illuminate\Validation\Validator;
use Override;
use Tests\Fixtures\Rules\UppercaseValueRule;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class MutatedValidationMutator extends AbstractValidatorMutator
{
    #[Override()]
    public function rules(array $input, ClassMetadata $metadata): array
    {
        return [
            'name' => ['min:5'],
            'code' => [new UppercaseValueRule()],
        ];
    }

    #[Override()]
    public function messages(array $input, ClassMetadata $metadata): array
    {
        return [
            'name.min' => 'The :attribute must contain at least 5 characters.',
        ];
    }

    #[Override()]
    public function attributes(array $input, ClassMetadata $metadata): array
    {
        return [
            'name' => 'display name',
        ];
    }

    #[Override()]
    public function stopOnFirstFailure(array $input, ClassMetadata $metadata): bool
    {
        return true;
    }

    public function errorBag(array $input, ClassMetadata $metadata): string
    {
        return 'mutated-data';
    }

    public function withValidator(Validator $validator, array $input, ClassMetadata $metadata): void
    {
        $validator->after(function (Validator $validator) use ($input): void {
            if (!(($input['forbidden'] ?? null) === 'blocked')) {
                return;
            }

            $validator->errors()->add('payload', 'The selected payload is blocked.');
        });
    }
}
