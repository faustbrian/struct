<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support;

use Cline\Struct\Contracts\InfersValidationRules;
use Cline\Struct\Metadata\ClassMetadata;
use Cline\Struct\Metadata\PropertyMetadata;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class CountingRuleInferrer implements InfersValidationRules
{
    public int $propertyCalls = 0;

    public int $itemCalls = 0;

    public function handle(ClassMetadata $metadata, PropertyMetadata $property): array
    {
        ++$this->propertyCalls;

        return ['sometimes'];
    }

    public function handleItems(ClassMetadata $metadata, PropertyMetadata $property): array
    {
        ++$this->itemCalls;

        return [];
    }
}
