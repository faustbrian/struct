<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Casts;

use Cline\Struct\Contracts\ContextualCastInterface;
use Cline\Struct\Metadata\PropertyMetadata;
use Cline\Struct\Support\PropertyHydrationContext;

use function mb_strtolower;
use function mb_strtoupper;
use function spl_object_id;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class ContextualValueCast implements ContextualCastInterface
{
    /** @var list<array{context: PropertyHydrationContext, contextId: int, dataClass: string, property: string, rawInput: array<string, mixed>, resolvedProperties: array<string, mixed>}> */
    public static array $observations = [];

    public static function reset(): void
    {
        self::$observations = [];
    }

    public function get(PropertyMetadata $property, mixed $value): mixed
    {
        return $value;
    }

    public function getWithContext(
        PropertyMetadata $property,
        mixed $value,
        PropertyHydrationContext $context,
    ): mixed {
        self::$observations[] = [
            'context' => $context,
            'contextId' => spl_object_id($context),
            'dataClass' => $context->dataClass,
            'property' => $context->property->name,
            'rawInput' => $context->rawInput,
            'resolvedProperties' => $context->resolvedProperties,
        ];

        if (($context->resolvedProperties['mode'] ?? null) === 'upper') {
            return mb_strtoupper((string) $value);
        }

        if (($context->rawInput['prefix'] ?? null) === 'raw:') {
            return 'raw:'.$value;
        }

        return mb_strtolower((string) $value);
    }

    public function set(PropertyMetadata $property, mixed $value): mixed
    {
        return $value;
    }
}
