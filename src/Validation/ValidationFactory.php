<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Validation;

use Cline\Struct\Contracts\ValidatorMutatorInterface;
use Cline\Struct\Metadata\ClassMetadata;
use Illuminate\Support\Facades\Validator;

use function array_key_exists;
use function resolve;

/**
 * Builds a `PreparedValidator` for struct DTO metadata and request payload data.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class ValidationFactory
{
    /** @var array<class-string, array<string, array<int, mixed>>> */
    private array $inferredRules = [];

    /** @var array<class-string, null|ValidatorMutatorInterface> */
    private array $mutators = [];

    public function __construct(
        private readonly RuleInferrer $ruleInferrer = new RuleInferrer(),
    ) {}

    /**
     * Create and configure a validator instance using inferred rules and mutator data.
     *
     * @param array<string, mixed> $input
     */
    public function make(ClassMetadata $metadata, array $input): PreparedValidator
    {
        $mutator = $this->mutator($metadata);
        $validator = Validator::make(
            $input,
            $this->mergeRules(
                $this->inferredRules($metadata),
                $mutator?->rules($input, $metadata) ?? [],
            ),
            $mutator?->messages($input, $metadata) ?? [],
            $mutator?->attributes($input, $metadata) ?? [],
        );

        if ($mutator?->stopOnFirstFailure($input, $metadata) === true) {
            $validator->stopOnFirstFailure();
        }

        $mutator?->withValidator($validator, $input, $metadata);

        return new PreparedValidator(
            $validator,
            $mutator?->errorBag($input, $metadata),
        );
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function inferredRules(ClassMetadata $metadata): array
    {
        return $this->inferredRules[$metadata->class] ?? $this->inferredRules[$metadata->class] = $this->ruleInferrer->infer($metadata);
    }

    /**
     * Merge generated rules with additional mutator-provided rules.
     *
     * Additional rules for the same key are appended after base rules.
     *
     * @param  array<string, array<int, mixed>> $baseRules
     * @param  array<string, array<int, mixed>> $extraRules
     * @return array<string, array<int, mixed>>
     */
    private function mergeRules(array $baseRules, array $extraRules): array
    {
        $merged = $baseRules;

        foreach ($extraRules as $key => $rules) {
            $merged[$key] ??= [];

            foreach ($rules as $rule) {
                $merged[$key][] = $rule;
            }
        }

        return $merged;
    }

    /**
     * Resolve a validator mutator for the metadata when one is configured.
     *
     * Returns null when metadata does not define a mutator class.
     */
    private function mutator(ClassMetadata $metadata): ?ValidatorMutatorInterface
    {
        if ($metadata->validatorMutator === null) {
            return null;
        }

        if (array_key_exists($metadata->class, $this->mutators)) {
            return $this->mutators[$metadata->class];
        }

        $mutator = resolve($metadata->validatorMutator);

        if (!$mutator instanceof ValidatorMutatorInterface) {
            return $this->mutators[$metadata->class] = null;
        }

        return $this->mutators[$metadata->class] = $mutator;
    }
}
