<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Support;

use Cline\Struct\Metadata\PropertyMetadata;

/**
 * Holds immutable DTO-level context for one property hydration step.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class PropertyHydrationContext
{
    /**
     * @param class-string         $dataClass
     * @param array<string, mixed> $rawInput
     * @param array<string, mixed> $resolvedProperties
     */
    public function __construct(
        public string $dataClass,
        public PropertyMetadata $property,
        public array $rawInput,
        public array $resolvedProperties,
    ) {}
}
