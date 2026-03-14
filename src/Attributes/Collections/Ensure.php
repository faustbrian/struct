<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\Collections;

use Attribute;
use Cline\Struct\Contracts\TransformsLaravelCollectionValueInterface;
use Cline\Struct\Support\CreationContext;
use Illuminate\Support\Collection;
use UnexpectedValueException;

use function get_debug_type;
use function implode;
use function is_array;
use function sprintf;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Ensure implements TransformsLaravelCollectionValueInterface
{
    /**
     * @param 'array'|'bool'|'float'|'int'|'null'|'string'|array<array-key, 'array'|'bool'|'float'|'int'|'null'|'string'|class-string>|class-string $type
     */
    public function __construct(
        public string|array $type,
    ) {}

    public function transformCollection(Collection $items, ?CreationContext $context = null): Collection
    {
        $allowedTypes = is_array($this->type) ? $this->type : [$this->type];

        return $items->each(static function (mixed $item, int|string $index) use ($allowedTypes): void {
            $itemType = get_debug_type($item);

            foreach ($allowedTypes as $allowedType) {
                if ($itemType === $allowedType || ($allowedType !== 'null' && $item instanceof $allowedType)) {
                    return;
                }
            }

            throw new UnexpectedValueException(
                sprintf(
                    "Collection should only include [%s] items, but '%s' found at position %s.",
                    implode(', ', $allowedTypes),
                    $itemType,
                    (string) $index,
                ),
            );
        });
    }
}
