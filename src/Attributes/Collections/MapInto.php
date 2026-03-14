<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\Collections;

use Attribute;
use Cline\Struct\AbstractData;
use Cline\Struct\Contracts\TransformsLaravelCollectionValueInterface;
use Cline\Struct\Support\CreationContext;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use stdClass;

use function array_is_list;
use function get_object_vars;
use function is_a;
use function is_array;
use function is_object;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class MapInto implements TransformsLaravelCollectionValueInterface
{
    /**
     * @param class-string $class
     */
    public function __construct(
        public string $class,
    ) {}

    public function transformCollection(Collection $items, ?CreationContext $context = null): Collection
    {
        return $items->map(function (mixed $value): mixed {
            if ($value instanceof $this->class) {
                return $value;
            }

            if (!is_a($this->class, AbstractData::class, true)) {
                return $value;
            }

            /** @var class-string<AbstractData> $class */
            $class = $this->class;

            if ($value instanceof Arrayable) {
                return $class::create($this->normalizePayload($value->toArray()));
            }

            if (is_array($value)) {
                return $class::create($this->normalizePayload($value));
            }

            if ($value instanceof stdClass) {
                return $class::create($this->normalizePayload((array) $value));
            }

            if (is_object($value)) {
                return $class::create($this->normalizePayload(get_object_vars($value)));
            }

            return $class::create(['value' => $value]);
        });
    }

    /**
     * @param  array<array-key, mixed> $payload
     * @return array<string, mixed>
     */
    private function normalizePayload(array $payload): array
    {
        if (array_is_list($payload)) {
            return ['value' => $payload];
        }

        $normalized = [];

        foreach ($payload as $key => $value) {
            $normalized[(string) $key] = $value;
        }

        return $normalized;
    }
}
