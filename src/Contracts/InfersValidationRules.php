<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

use Cline\Struct\Metadata\ClassMetadata;
use Cline\Struct\Metadata\PropertyMetadata;

/**
 * Contributes inferred validation rules for data object properties.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface InfersValidationRules
{
    /**
     * @return array<int, mixed>
     */
    public function handle(ClassMetadata $metadata, PropertyMetadata $property): array;

    /**
     * @return array<int, mixed>
     */
    public function handleItems(ClassMetadata $metadata, PropertyMetadata $property): array;
}
