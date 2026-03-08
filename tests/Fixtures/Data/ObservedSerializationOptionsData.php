<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Serialization\SerializationContext;
use Cline\Struct\Serialization\SerializationOptions;
use Override;
use Tests\Fixtures\Support\SerializationOptionsTracker;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class ObservedSerializationOptionsData extends AbstractData
{
    public function __construct(
        public string $value,
    ) {}

    #[Override()]
    public function toArray(
        bool $includeSensitive = false,
        array $include = [],
        array $exclude = [],
        array $groups = [],
        array $context = [],
        ?SerializationOptions $serialization = null,
    ): array {
        SerializationOptionsTracker::$seen[] = $serialization;

        return parent::toArray(
            includeSensitive: $includeSensitive,
            include: $include,
            exclude: $exclude,
            groups: $groups,
            context: $context,
            serialization: $serialization,
        );
    }

    #[Override()]
    public function toArrayUsingContext(
        SerializationContext $context,
    ): array {
        SerializationOptionsTracker::$seen[] = $context->options;

        return parent::toArrayUsingContext($context);
    }
}
