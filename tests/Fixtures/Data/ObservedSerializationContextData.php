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
use Tests\Fixtures\Support\SerializationContextTracker;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class ObservedSerializationContextData extends AbstractData
{
    public function __construct(
        public string $value,
    ) {}

    #[Override()]
    public function toArrayUsingContext(
        SerializationContext $context,
    ): array {
        SerializationContextTracker::$seen[] = $context;

        return parent::toArrayUsingContext($context);
    }
}
