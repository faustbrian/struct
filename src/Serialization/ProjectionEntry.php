<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Serialization;

use Cline\Struct\Metadata\PropertyMetadata;

/**
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 * @internal
 */
final readonly class ProjectionEntry
{
    public function __construct(
        public PropertyMetadata $property,
        public bool $conditional,
        public bool $derived,
    ) {}
}
