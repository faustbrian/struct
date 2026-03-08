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

/**
 * Adds sometimes rules for properties that may be omitted.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class SometimesRuleInferrer implements InfersValidationRules
{
    public function handle(ClassMetadata $metadata, PropertyMetadata $property): array
    {
        if (
            !$property->inferValidationRules
            || (!$property->isOptional && !$property->hasDefaultValue && !($property->nullable && !$metadata->forbidUndefinedValues))
        ) {
            return [];
        }

        return ['sometimes'];
    }

    public function handleItems(ClassMetadata $metadata, PropertyMetadata $property): array
    {
        return [];
    }
}
