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
 * Adds required rules for properties that must be present.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class RequiredRuleInferrer implements InfersValidationRules
{
    public function handle(ClassMetadata $metadata, PropertyMetadata $property): array
    {
        if (!$property->inferValidationRules) {
            return [];
        }

        if ($property->isOptional || $property->hasDefaultValue || ($property->nullable && !$metadata->forbidUndefinedValues)) {
            return [];
        }

        return ['required'];
    }

    public function handleItems(ClassMetadata $metadata, PropertyMetadata $property): array
    {
        return [];
    }
}
