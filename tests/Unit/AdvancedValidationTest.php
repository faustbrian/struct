<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Exceptions\DataValidationException;
use Cline\Struct\Validation\BuiltInTypesRuleInferrer;
use Cline\Struct\Validation\NullableRuleInferrer;
use Cline\Struct\Validation\RequiredRuleInferrer;
use Cline\Struct\Validation\SometimesRuleInferrer;
use Illuminate\Contracts\Config\Repository;
use Tests\Fixtures\Data\CascadingRootData;
use Tests\Fixtures\Data\CustomValidateArraySyntaxData;
use Tests\Fixtures\Data\IgnoredInferredValidationData;
use Tests\Fixtures\Data\InferredValidationData;
use Tests\Fixtures\Data\ItemValidatedData;
use Tests\Fixtures\Data\MappedUserData;
use Tests\Fixtures\Data\MutatedValidationData;
use Tests\Fixtures\Data\NoInferredValidationData;
use Tests\Fixtures\Data\OptedInInferredValidationData;

describe('Advanced validation', function (): void {
    beforeEach(function (): void {
        // Arrange
        $this->configRepository = resolve(Repository::class);
        $this->previousInferRules = $this->configRepository->get('struct.validation.infer_rules', true);
        $this->previousRuleInferrers = $this->configRepository->get('struct.validation.rule_inferrers');
        $this->configRepository->set('struct.validation.infer_rules', true);
    });

    afterEach(function (): void {
        // Arrange
        $this->configRepository->set('struct.validation.infer_rules', $this->previousInferRules);
        $this->configRepository->set('struct.validation.rule_inferrers', $this->previousRuleInferrers);
    });

    describe('Happy', function (): void {
        test('creates dto when whole object validation is not inferred', function (): void {
            // Arrange
            // Act
            $dto = NoInferredValidationData::createWithValidation([
                'name' => 123,
                'age' => 'abc',
            ]);

            // Assert
            expect($dto->name)->toBe('123')
                ->and($dto->age)->toBe('abc');
        });

        test('creates dto when single property validation is not inferred', function (): void {
            // Arrange
            // Act
            $dto = IgnoredInferredValidationData::createWithValidation([
                'name' => 123,
                'age' => 40,
            ]);

            // Assert
            expect($dto->name)->toBe('123')
                ->and($dto->age)->toBe(40);
        });
    });

    describe('Sad', function (): void {
        test('throws when inferred validation fails for unsupported property values', function (): void {
            // Arrange
            // Act
            InferredValidationData::createWithValidation([
                'name' => 123,
                'age' => 'abc',
                'active' => 'not-a-bool',
                'status' => 'missing',
                'published_at' => 'not-a-date',
                'scores' => ['1', 'nope'],
            ]);
        })->throws(DataValidationException::class);

        test('throws when validate array syntax with strings and rule objects fails', function (): void {
            // Arrange
            // Act
            try {
                CustomValidateArraySyntaxData::createWithValidation([
                    'name' => 'ok',
                    'code' => 'lowercase',
                ]);

                expect(false)->toBeTrue('Validation should have failed.');
            } catch (DataValidationException $dataValidationException) {
                // Assert
                expect($dataValidationException->errors())
                    ->toHaveKeys(['name', 'code']);
            }
        });

        test('throws when manual validator mutator rules are not satisfied', function (): void {
            // Arrange
            // Act
            try {
                MutatedValidationData::createWithValidation([
                    'name' => 'abc',
                    'code' => 'lowercase',
                ]);

                expect(false)->toBeTrue('Validation should have failed.');
            } catch (DataValidationException $dataValidationException) {
                // Assert
                expect($dataValidationException->getMessage())->toBe('The display name must contain at least 5 characters.')
                    ->and($dataValidationException->errors())->toHaveCount(1)
                    ->and($dataValidationException->errorBag())->toBe('mutated-data');
            }
        });

        test('throws when custom rule objects reject forbidden payload values', function (): void {
            // Arrange
            // Act
            try {
                MutatedValidationData::createWithValidation([
                    'name' => 'valid name',
                    'code' => 'UPPER',
                    'forbidden' => 'blocked',
                ]);

                expect(false)->toBeTrue('Validation should have failed.');
            } catch (DataValidationException $dataValidationException) {
                // Assert
                expect($dataValidationException->getMessage())->toBe('The selected payload is blocked.')
                    ->and($dataValidationException->errors())->toHaveKey('payload');
            }
        });

        test('throws for nested data object and collection validation failures', function (): void {
            // Arrange
            // Act
            CascadingRootData::createWithValidation([
                'title' => 'Valid title',
                'profile' => [
                    'name' => 'Ok',
                    'age' => 'abc',
                ],
                'contacts' => [
                    [
                        'name' => 'Friend',
                        'age' => 30,
                    ],
                    [
                        'name' => 'No',
                        'age' => 20,
                    ],
                ],
            ]);
        })->throws(DataValidationException::class);

        test('throws when collection item rules reject invalid values', function (): void {
            // Arrange
            // Act
            try {
                ItemValidatedData::createWithValidation([
                    'scores' => [10, 5],
                    'codes' => ['UPPER', 'lower'],
                ]);

                expect(false)->toBeTrue('Validation should have failed.');
            } catch (DataValidationException $dataValidationException) {
                // Assert
                expect($dataValidationException->errors())
                    ->toHaveKeys(['scores.1', 'codes.1']);
            }
        });
    });

    describe('Edge', function (): void {
        test('respects per-class opt-in when global inferred rules are disabled', function (): void {
            // Arrange
            $this->configRepository->set('struct.validation.infer_rules', false);

            // Act
            $dto = NoInferredValidationData::createWithValidation([
                'name' => 123,
                'age' => 'abc',
            ]);

            // Assert
            expect($dto->name)->toBe('123')
                ->and($dto->age)->toBe('abc');

            OptedInInferredValidationData::createWithValidation([
                'name' => 123,
                'age' => 'abc',
            ]);
        })->throws(DataValidationException::class);

        test('uses configurable rule inferrers when building inferred validation rules', function (): void {
            // Arrange
            $this->configRepository->set('struct.validation.rule_inferrers', [
                SometimesRuleInferrer::class,
                BuiltInTypesRuleInferrer::class,
                NullableRuleInferrer::class,
                RequiredRuleInferrer::class,
            ]);

            // Act
            $dto = MappedUserData::createWithValidation([
                'id' => 1,
                'full_name' => 'No',
                'created_at' => '2026-03-07T10:00:00+00:00',
                'status' => 'active',
            ]);

            // Assert
            expect($dto->fullName)->toBe('No');
        });
    });
});
