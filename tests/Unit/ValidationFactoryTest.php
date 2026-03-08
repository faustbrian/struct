<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Metadata\MetadataFactory;
use Cline\Struct\Validation\RuleInferrer;
use Cline\Struct\Validation\ValidationFactory;
use Tests\Fixtures\Data\CountedValidationData;
use Tests\Fixtures\Data\MappedUserData;
use Tests\Fixtures\Data\MutatedValidationData;
use Tests\Fixtures\Support\CountingRuleInferrer;
use Tests\Fixtures\Validation\CountingValidationMutator;

describe('ValidationFactory', function (): void {
    test('caches inferred base rules per metadata instance', function (): void {
        $inferrer = new CountingRuleInferrer();
        $factory = new ValidationFactory(
            new RuleInferrer([$inferrer]),
        );
        $metadata = app()->make(MetadataFactory::class)
            ->for(MappedUserData::class);
        $input = [
            'id' => 1,
            'full_name' => 'Brian',
            'created_at' => '2026-03-07T10:00:00+00:00',
            'status' => 'active',
            'tags' => [1, 2],
        ];

        $factory->make($metadata, $input);

        expect($inferrer->propertyCalls)->toBe(count($metadata->properties))
            ->and($inferrer->itemCalls)->toBe(count($metadata->properties));

        $factory->make($metadata, $input);

        expect($inferrer->propertyCalls)->toBe(count($metadata->properties))
            ->and($inferrer->itemCalls)->toBe(count($metadata->properties));
    });

    test('appends mutator rules after inferred base rules', function (): void {
        $factory = new ValidationFactory();
        $metadata = app()->make(MetadataFactory::class)
            ->for(MutatedValidationData::class);
        $prepared = $factory->make($metadata, [
            'name' => 'Brian',
            'code' => 'UPPER',
        ]);
        $rules = $prepared->validator->getRules();

        expect($rules['name'])->toBe(['string', 'required', 'min:5'])
            ->and($rules['code'])->toHaveCount(3)
            ->and($rules['code'][2])->toBeObject();
    });

    test('caches resolved validator mutators per metadata class', function (): void {
        CountingValidationMutator::$instances = 0;
        app()->bind(
            CountingValidationMutator::class,
            static fn (): CountingValidationMutator => new CountingValidationMutator(),
        );

        $factory = new ValidationFactory();
        $metadata = app()->make(MetadataFactory::class)
            ->for(CountedValidationData::class);
        $input = ['name' => 'Brian'];

        $factory->make($metadata, $input);
        $factory->make($metadata, $input);

        expect(CountingValidationMutator::$instances)->toBe(1);
    });
});
