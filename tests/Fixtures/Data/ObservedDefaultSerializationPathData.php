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
use Override;
use Tests\Fixtures\Support\DefaultSerializationPathTracker;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class ObservedDefaultSerializationPathData extends AbstractData
{
    public function __construct(
        public string $value,
    ) {}

    #[Override()]
    protected function serializePayload(SerializationContext $context): array
    {
        ++DefaultSerializationPathTracker::$genericCalls;

        return parent::serializePayload($context);
    }

    #[Override()]
    protected function serializeDefaultPayloadUsingContext(SerializationContext $context): array
    {
        ++DefaultSerializationPathTracker::$defaultCalls;

        return parent::serializeDefaultPayloadUsingContext($context);
    }
}
