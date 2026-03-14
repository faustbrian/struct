<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\CollectionSources;

use Attribute;
use Cline\Struct\Support\CreationContext;
use Illuminate\Support\Collection;

use function array_key_exists;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class FromJson extends AbstractCollectionSourceAttribute
{
    public function __construct(
        public ?string $source = null,
        public ?string $json = null,
        public int $depth = 512,
        public int $flags = 0,
    ) {}

    public function generateCollection(array $properties, ?CreationContext $context = null): Collection
    {
        if ($this->source !== null && array_key_exists($this->source, $properties)) {
            return Collection::fromJson((string) $properties[$this->source], $this->depth, $this->flags);
        }

        return Collection::fromJson((string) $this->json, $this->depth, $this->flags);
    }
}
