<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Validation;

use Cline\Struct\Contracts\InfersValidationRules;
use Cline\Struct\Metadata\ClassMetadata;
use Cline\Struct\Metadata\PropertyMetadata;
use Throwable;

use function config;
use function function_exists;
use function is_a;
use function is_array;
use function is_string;
use function resolve;

/**
 * Infers Laravel validation rules from metadata produced by struct data classes.
 *
 * Combines presence checks, inferred type rules and explicit property rules.
 * @author Brian Faust <brian@cline.sh>
 */
final class RuleInferrer
{
    /**
     * @param array<int, InfersValidationRules> $ruleInferrers
     */
    public function __construct(
        private array $ruleInferrers = [],
    ) {
        if ($this->ruleInferrers !== []) {
            return;
        }

        $this->ruleInferrers = $this->defaultRuleInferrers();
    }

    /**
     * Build validation rules for all properties in the provided class metadata.
     *
     * @return array<string, array<int, mixed>>
     */
    public function infer(ClassMetadata $metadata): array
    {
        $rules = [];

        foreach ($metadata->properties as $property) {
            if ($property->isComputed || $property->hasCollectionResultAttribute || $property->hasCollectionSourceAttribute) {
                continue;
            }

            $propertyRules = $this->propertyRules($metadata, $property);

            if ($propertyRules !== []) {
                $rules[$property->inputName] = $propertyRules;
            }

            $itemRules = $this->itemRules($metadata, $property);

            if ($itemRules === []) {
                continue;
            }

            $rules[$property->inputName.'.*'] = $itemRules;
        }

        return $rules;
    }

    /**
     * @return array<int, mixed>
     */
    private function propertyRules(ClassMetadata $metadata, PropertyMetadata $property): array
    {
        $rules = [];

        foreach ($this->ruleInferrers as $ruleInferrer) {
            foreach ($ruleInferrer->handle($metadata, $property) as $rule) {
                $rules[] = $rule;
            }
        }

        return $rules;
    }

    /**
     * @return array<int, mixed>
     */
    private function itemRules(ClassMetadata $metadata, PropertyMetadata $property): array
    {
        $rules = [];

        foreach ($this->ruleInferrers as $ruleInferrer) {
            foreach ($ruleInferrer->handleItems($metadata, $property) as $rule) {
                $rules[] = $rule;
            }
        }

        return $rules;
    }

    /**
     * @return array<int, InfersValidationRules>
     */
    private function defaultRuleInferrers(): array
    {
        $configured = function_exists('config')
            ? config('struct.validation.rule_inferrers', [
                SometimesRuleInferrer::class,
                BuiltInTypesRuleInferrer::class,
                AttributesRuleInferrer::class,
                NullableRuleInferrer::class,
                RequiredRuleInferrer::class,
            ])
            : [
                SometimesRuleInferrer::class,
                BuiltInTypesRuleInferrer::class,
                AttributesRuleInferrer::class,
                NullableRuleInferrer::class,
                RequiredRuleInferrer::class,
            ];

        if (!is_array($configured)) {
            return [];
        }

        $ruleInferrers = [];

        foreach ($configured as $configuredRuleInferrer) {
            if (!is_string($configuredRuleInferrer)) {
                continue;
            }

            if (!is_a($configuredRuleInferrer, InfersValidationRules::class, true)) {
                continue;
            }

            try {
                $instance = function_exists('resolve')
                    ? resolve($configuredRuleInferrer)
                    : new $configuredRuleInferrer();
            } catch (Throwable) {
                $instance = new $configuredRuleInferrer();
            }

            $ruleInferrers[] = $instance;
        }

        return $ruleInferrers;
    }
}
