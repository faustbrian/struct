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
 * Applies explicit validation rules declared on DTO properties.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class AttributesRuleInferrer implements InfersValidationRules
{
    public function handle(ClassMetadata $metadata, PropertyMetadata $property): array
    {
        return $property->validationRules;
    }

    public function handleItems(ClassMetadata $metadata, PropertyMetadata $property): array
    {
        return $property->itemValidationRules;
    }
}
