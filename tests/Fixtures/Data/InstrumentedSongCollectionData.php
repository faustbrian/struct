<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\AsDataCollection;
use Cline\Struct\Metadata\PropertyMetadata;
use Cline\Struct\Serialization\SerializationContext;
use Cline\Struct\Support\CreationContext;
use Cline\Struct\Support\DataCollection;
use Override;
use Tests\Fixtures\Support\CollectionItemPropertyTracker;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class InstrumentedSongCollectionData extends AbstractData
{
    public function __construct(
        #[AsDataCollection(SongData::class)]
        public DataCollection $songs,
    ) {}

    #[Override()]
    protected static function collectionItemProperty(
        PropertyMetadata $property,
        ?CreationContext $context = null,
        ?SerializationContext $serializationContext = null,
    ): PropertyMetadata {
        ++CollectionItemPropertyTracker::$calls;

        return parent::collectionItemProperty(
            $property,
            context: $context,
            serializationContext: $serializationContext,
        );
    }
}
