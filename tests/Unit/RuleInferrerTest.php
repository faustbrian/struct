<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Contracts\InfersValidationRules;
use Cline\Struct\Metadata\ClassMetadata;
use Cline\Struct\Metadata\MetadataFactory;
use Cline\Struct\Metadata\PropertyMetadata;
use Cline\Struct\Validation\RuleInferrer;
use Tests\Fixtures\Data\ItemValidatedData;

describe('RuleInferrer', function (): void {
    test('preserves inferred rule order for properties and items', function (): void {
        $metadata = resolve(MetadataFactory::class)->for(ItemValidatedData::class);
        $inferrer = new RuleInferrer([
            new class() implements InfersValidationRules
            {
                public function handle(ClassMetadata $metadata, PropertyMetadata $property): array
                {
                    return ['first', 'second'];
                }

                public function handleItems(ClassMetadata $metadata, PropertyMetadata $property): array
                {
                    return ['item-first'];
                }
            },
            new class() implements InfersValidationRules
            {
                public function handle(ClassMetadata $metadata, PropertyMetadata $property): array
                {
                    return ['third'];
                }

                public function handleItems(ClassMetadata $metadata, PropertyMetadata $property): array
                {
                    return ['item-second', 'item-third'];
                }
            },
        ]);

        expect($inferrer->infer($metadata))->toBe([
            'scores' => ['first', 'second', 'third'],
            'scores.*' => ['item-first', 'item-second', 'item-third'],
            'codes' => ['first', 'second', 'third'],
            'codes.*' => ['item-first', 'item-second', 'item-third'],
        ]);
    });
});
