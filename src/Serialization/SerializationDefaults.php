<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Serialization;

use Cline\Struct\Metadata\MetadataFactory;

/**
 * Shares default serialization services for the hot no-options path.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @internal
 * @psalm-immutable
 */
final readonly class SerializationDefaults
{
    public function __construct(
        public SerializationOptions $options,
        public MetadataFactory $metadataFactory,
    ) {}
}
