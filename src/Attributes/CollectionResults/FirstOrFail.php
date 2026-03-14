<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\CollectionResults;

use Attribute;
use Cline\Struct\Support\CreationContext;
use Illuminate\Support\Collection;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class FirstOrFail extends AbstractCollectionResultAttribute
{
    public function __construct(
        string $source,
        public mixed $key = null,
        public mixed $operator = null,
        public mixed $value = null,
    ) {
        parent::__construct($source);
    }

    public function computeResult(Collection $items, array $properties, ?CreationContext $context = null): mixed
    {
        if ($this->value !== null) {
            return $items->firstOrFail($this->key, $this->operator, $this->value);
        }

        if ($this->operator !== null) {
            return $items->firstOrFail($this->key, $this->operator);
        }

        return $items->firstOrFail($this->key);
    }
}
