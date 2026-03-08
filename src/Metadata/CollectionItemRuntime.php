<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Metadata;

use Cline\Struct\Contracts\CastInterface;

/**
 * @author Brian Faust <brian@cline.sh>
 * @internal
 */
final class CollectionItemRuntime
{
    private ?PropertyMetadata $property = null;

    public function __construct(
        private readonly PropertyMetadata $source,
        private readonly ?CollectionItemDescriptor $descriptor,
    ) {}

    public function cast(): ?CastInterface
    {
        if ($this->descriptor instanceof CollectionItemDescriptor) {
            return $this->descriptor->cast ?? $this->source->collectionItemCast();
        }

        return $this->source->collectionItemCast();
    }

    /**
     * @return list<string>
     */
    public function types(): array
    {
        if ($this->descriptor instanceof CollectionItemDescriptor) {
            return $this->descriptor->types;
        }

        return $this->source->collectionItemTypes();
    }

    public function typeKindAt(int $index): string
    {
        return $this->descriptor?->typeKinds[$index]
            ?? $this->source->collectionItemTypeKinds()[$index]
            ?? 'other';
    }

    public function property(): PropertyMetadata
    {
        return $this->property ??= $this->source->forCollectionItem();
    }
}
